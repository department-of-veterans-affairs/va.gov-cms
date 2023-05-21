<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;

/**
 * Class PostFacilityService posts VetCenter CAP info and status to Lighthouse.
 */
class PostFacilityVetCenterCap extends PostFacilityBase {

  const STATE_ARCHIVED = 'archived';
  const STATE_DRAFT = 'draft';
  const STATE_PUBLISHED = 'published';

  /**
   * A array of any errors in prepping the data.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * The facility node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $facilityNode;

  /**
   * Temp storage of the status that should be pushed.
   *
   * @var string
   */
  protected $statusToPush;

  /**
   * Temp storage of the status additional info that should be pushed.
   *
   * @var string
   */
  protected $additionalInfoToPush;

  /**
   * The facilities that are born in the CMS.
   *
   * @var array
   */
  protected $facilitiesSourcedFromCms = [
    'vet_center_cap' => [
      'type' => 'vet_center',
      'classification' => 'Community Access Point',
    ],
  ];

  /**
   * Adds facility data to Post API queue.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param bool $forcePush
   *   Force processing of facility status if true.
   *
   * @return int
   *   The count of the number of items queued (1,0).
   */
  public function queueFacility(EntityInterface $entity, bool $forcePush = FALSE) {
    $queued_count = 0;

    if ($entity instanceof NodeInterface && $this->isFacilitySourccedFromCms($entity)) {
      /** @var \Drupal\node\NodeInterface $entity*/
      $this->facilityNode = $entity;
      $facility_id = $entity->hasField('field_facility_locator_api_id') ? $entity->get('field_facility_locator_api_id')->value : NULL;
      $data['nid'] = $entity->id();
      // Queue item's Unique ID.
      $data['uid'] = $facility_id ? "facility_{$facility_id}" : NULL;

      // Set a key based on which the endpoint will be
      // defined during queue execution.
      $data['endpoint_path'] = ($facility_id) ? "/services/va_facilities/v1/facilities/{$facility_id}/cms-crud-overlay" : NULL;

      $data['payload'] = $this->getPayload($forcePush);

      // Only add to queue if payload is not empty.
      // If its empty, it means that there is no new information to send to
      // endpoint.
      if (!empty($data['payload']) && $facility_id) {
        $this->postQueue->addToQueue($data, $this->shouldDedupe());
        $queued_count = 1;
        $message = $this->t('The facility info for %content_type: %facility_name is being sent to the Facility Locator.', $this->getMessageVars());
        $this->messenger->addStatus($message);
      }
      elseif (empty($facility_id)) {
        // Log error on empty Facility Locator API ID.
        $message = $this->t('Post API: attempted to add an item with NID %nid to queue, but it had no Facility API ID.', $this->getMessageVars());
        $this->loggerChannelFactory->get('va_gov_post_api')->error($message);
      }
    }
    $this->nuke();
    return $queued_count;
  }

  /**
   * Add facility data to Post API queue by VetCenter.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return int
   *   The count of the number of items queued.
   */
  public function queueSystemRelatedFacilities(EntityInterface $entity) {
    $queued_count = 0;
    if (($entity->getEntityTypeId() === 'node')
    && ($this->shouldPushSystem($entity))) {
      // Not sure if this needs to be based of the parent vet center.   if it is not published. the cap should not be either.
    // $query = $this->entityTypeManager->getStorage('node')->getQuery();
    // $nids = $query->condition('type', 'health_care_local_facility')
    //   ->condition('field_region_page', $entity->id())
    //   ->condition('status', 1)
    //   ->execute();

      $vamc_facility_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      foreach ($vamc_facility_nodes as $node) {
        // Process each VAMC Facility referencing this node.
        $queued_count += $this->queueFacilityStatus($node, TRUE);
      }
    }

    return $queued_count;
  }

  /**
   * Get the message variables and values.
   *
   * @return array
   *   An array of message values keyed by replacement token.
   */
  protected function getMessageVars(): array {
    return [
      '%content_type' => $this->facilityNode->get('type')->entity->label(),
      '%facility_name' => $this->facilityNode->getTitle(),
      '%nid' => $this->facilityNode->id(),
    ];
  }

  /**
   * Compose and return payload array for facility status.
   *
   * @param bool $forcePush
   *   Force processing of facility status if true.
   *
   * @return array
   *   Payload array.
   */
  protected function getPayload(bool $forcePush = FALSE): array {
    // Default payload is an empty array.
    $payload = [];

    // Current field values.
    $this->statusToPush = $this->facilityNode->get('field_operating_status_facility')->value;
    $this->additionalInfoToPush = $this->getOperatingStatusMoreInfoShort();

    if (empty($this->statusToPush)) {
      // We can not send this without a status, so bail out.
      // It is a required field in the CMS but we want to be completely sure
      // Because it is required in lighthouse too.
      return $payload;
    }

    if ($this->shouldPush($forcePush)) {
      $facility = new \stdClass();
      $facility->name = $this->facilityNode->getTitle();
      $facility->id = $this->facilityNode->field_facility_locator_api_id->value;
      $facility->facility_type = $this->getFacilityType();
      $facility->classification = $this->getFacilityClassification();
      $facility->active_status = $this->facilityNode->isPublished();
      $facility->website = $this->getFacilityUrl()
      $facility->vet_center = $this->getParentVetCenter();
      $facility->operating_status = [
        'code' => strtoupper($this->statusToPush),
        'additional_info' => (strtoupper($this->statusToPush) != 'NORMAL') ? $this->additionalInfoToPush : NULL,
      ];
      $facility->geographical_identifier = ;//field_geographical_identifier;
      $facility->address = $this->getAddress();
      $facility->geometry = $this->getGeometry();
      $facility->call_for_hours = $this->getCallForHours();
      $facility->getHours();

      $payload = [
        'facility' => [$facility],
      ];
    }

    return $payload;
  }

  /**
   * Gets the type of facility.
   *
   * @return string|null
   *   The Facility API type.
   */
  protected function getFacilityType() {
    $bundle = $this->facilityNode->bundle();
    return $this->facilitiesSourcedFromCms[$bundle]['type'] ?? NULL;
  }

  /**
   * Gets the classificaion of the facility.
   *
   * @return string|null
   *   The Facility API classification.
   */
  protected function getFacilityClassification() {
    $bundle = $this->facilityNode->bundle();
    return $this->facilitiesSourcedFromCms[$bundle]['classification'] ?? NULL;
  }

  protected function getParentVetCenter() {
    // via  field_office (the parent vet center)
     {
      "name": "Jupiter Vet Center",
      "url": "https://www.va.gov/jupiter-vet-center",
      "phone": "561-422-1220"
    }
  }

  protected function getAddress() {
          // field_address
    {
      "address_organization": "Port St. Lucie VA Clinic",
      "address_line1": "126 SW Chamber Court",
      "address_line2": null,
      "state": "FL",
      "country_code": "US",
      "city": "Port St. Lucie",
      "zip_code": "34986",
    },
  }

  protected function getCallForHours() {
    //field_vetcenter_cap_hours_opt_in (0 Veterans should call main Vet Center for hours) (1 Provide CAP hours online)
  }

  protected function getHours () {
    // Should return empty if field_vetcenter_cap_hours_opt_in =0.
    // // field_office_hours
    {
        "Monday": "1100AM-200PM",
        "Tuesday": "1100AM-200PM",
        "Wednesday": "1100AM-200PM",
        "Thursday": "Closed",
        "Friday": "Closed",
        "Saturday": "Closed",
        "Sunday": "Closed"
      }
  }

  /**
   * Gets an entry for the geolocation
   *
   * @return object|null
   * The type point and coordinates or NULL if undefined.
   */
  protected function getGeometry() {
    // via  field_geolocation
     {
        "type": "Point",
        "coordinates": [
            -80.40251,
            27.31034
        ]
    },
  }

  /**
   * Builds the appropriate url for a facility.
   *
   * @return string|null
   *   A facility page wher this facility can be found.
   *   Null if something completely went wrong.
   */
  protected function getFacilityUrl(): string | null {
    $facility_url = NULL;
    $bundle = $this->facilityNode->bundle();
    if ($bundle === 'vet_center_cap') {
      // CAPs don't have their own page, so link to the parent facility's
      // locations page. Should look like this:
      // https://www.va.gov/big-vet-center/locations/#name-vet-center
      $parent_slug = '???';
      $name_fragment = $this->getNameFragment();
      $facility_url = "https://www.va.gov/{$parent_slug}/locations/#{$name_fragment}";
    }
    return $facility_url;
  }

  /**
   * Checks if the entity is a facility node that originates in CMS.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility with status info. FALSE otherwise.
   */
  protected function isFacilitySourccedFromCms(NodeInterface $entity) : bool {
    return in_array($entity->bundle(), $this->facilitiesSourcedFromCms);
  }

  /**
   * Determines if the data says a payload should be assembled and pushed.
   *
   * Potentially alters the pushed data based on what it evaluates.  This is
   * beyond its concern but can not be separated cleanly.
   *
   * @param bool $forcePush
   *   Force processing of facility status if true.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  protected function shouldPush(bool $forcePush = FALSE) {
    // Moderation state of what is being saved.
    $moderationState = $this->facilityNode->get('moderation_state')->value;
    $isArchived = ($moderationState === self::STATE_ARCHIVED) ? TRUE : FALSE;
    $thisRevisionIsPublished = $this->facilityNode->isPublished();
    $defaultRevision = $this->getDefaultRevision($this->facilityNode);
    $isNew = $this->facilityNode->isNew();
    $defaultRevisionIsPublished = $defaultRevision->isPublished();
    $statusChanged = $this->changedValue($this->facilityNode, $defaultRevision, 'field_operating_status_facility');
    $statusInfoChanged = $this->changedValue($this->facilityNode, $defaultRevision, 'field_operating_status_more_info');
    $supStatusChanged = $this->changedTarget($this->facilityNode, $defaultRevision, 'field_supplemental_status');
    $somethingChanged = $statusChanged || $statusInfoChanged || $supStatusChanged;

    // Case race. First to evaluate to TRUE wins.
    switch (TRUE) {
      case $this->isLovellTricareSection($this->facilityNode):
        // Node is part of the Lovell-Tricare section, do not push.
        $push = FALSE;
        break;

      case $forcePush && $thisRevisionIsPublished:
        // Forced push from updates to referenced entity.
      case $isNew:
        // A new node, should be pushed to initiate the value.
      case $thisRevisionIsPublished && $somethingChanged && $moderationState === self::STATE_PUBLISHED:
        // This revision is published and had a change, should be pushed.
      case $isArchived && $somethingChanged:
        // This node has been archived, got to push to remove it.
      case (!$defaultRevisionIsPublished && !$thisRevisionIsPublished && $somethingChanged):
        // Draft on an unpublished node, should be pushed.
        $push = TRUE;
        break;

      case ($defaultRevisionIsPublished && !$thisRevisionIsPublished && $moderationState === self::STATE_DRAFT):
        // Draft revision on published node, should not push, even w/bypass.
        $push = FALSE;
        break;

      case ($this->shouldBypass()):
        // Bypass is activated.
        // If it is on bypass this is a bulk push, which will be the default rev
        // so there is no risk of a draft status overriding published status.
        $push = TRUE;
        break;

      default:
        // Anything that makes it this far should not be pushed.
        $push = FALSE;
        break;
    }

    return $push;
  }

  /**
   * Determines if a VAMC system change merits a facility status push.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node VAMC system node we need to check push status for.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  protected function shouldPushSystem(NodeInterface $entity) {
    // If the name of the system changes we should push facility statuses.
    $moderationState = $entity->get('moderation_state')->value;
    $thisRevisionIsPublished = $entity->isPublished();
    $defaultRevision = $this->getDefaultRevision($entity);
    $nameChanged = $this->changedValue($entity, $defaultRevision, 'title');
    $phoneChanged = $this->changedValue($entity, $defaultRevision, 'field_va_health_connect_phone');
    $somethingChanged = $nameChanged || $phoneChanged;
    $push = FALSE;
    if ($thisRevisionIsPublished && $somethingChanged && $moderationState === self::STATE_PUBLISHED) {
      $push = TRUE;
    }
    return $push;
  }

  /**
   * Decides what to use as the previous/default revision and returns it.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node we need to find a default revision for.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  protected function getDefaultRevision(NodeInterface $entity) : NodeInterface {
    $hasOriginal = isset($entity->original) && ($entity->original instanceof EntityInterface);
    if ($entity->isNew()) {
      // There is no previous revision but to make comparison easier, set
      // the current node as the default.
      $defaultRevision = $entity;
    }
    elseif (($entity->get('moderation_state')->value === self::STATE_PUBLISHED || !$entity->isPublished()) && $hasOriginal) {
      // If it has never been published we just want the last save.
      // If the node is published, loading the default is an exact copy,
      // because the save already happened. Switch to using original.
      $defaultRevision = $entity->original;
    }
    else {
      $defaultRevision = $this->entityTypeManager->getStorage('node')->load($entity->id());
    }

    return $defaultRevision;
  }

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node we need to compare.
   * @param \Drupal\node\NodeInterface $revision
   *   The revision we are comparing to.
   * @param string $field_name
   *   The machine name of the field to check on.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedValue(NodeInterface $node, NodeInterface $revision, $field_name): bool {
    $value = $node->get($field_name)->value;
    $original_value = $revision->get($field_name)->value;

    return $value !== $original_value;
  }

  /**
   * Checks if the target_id of the field on the node changed.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node we need to compare.
   * @param \Drupal\node\NodeInterface $revision
   *   The revision we are comparing to.
   * @param string $field_name
   *   The machine name of the field to check on.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedTarget(NodeInterface $node, NodeInterface $revision, $field_name): bool {
    if ($node->hasField($field_name)) {
      $value = $node->get($field_name)->target_id;
      $original_value = $revision->get($field_name)->target_id;

      return $value !== $original_value;
    }
    return FALSE;
  }

  /**
   * Removes values from anything that should not be kept in state.
   */
  protected function nuke() : void {
    $this->statusToPush = NULL;
    $this->additionalInfoToPush = NULL;
    unset($this->facilityNode);
  }

}
