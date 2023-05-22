<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;

/**
 * Class PostFacilityCmsSourced posts CMS sourced info and status to Lighthouse.
 */
class PostFacilityCmsSourced extends PostFacilityBase {

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

    if ($entity instanceof NodeInterface && $this->isFacilitySourcedFromCms($entity)) {
      /** @var \Drupal\node\NodeInterface $entity*/
      $this->facilityNode = $entity;
      $facility_id = $entity->hasField('field_facility_locator_api_id') ? $entity->get('field_facility_locator_api_id')->value : NULL;
      $data['nid'] = $entity->id();
      // Queue item's Unique ID.
      $data['uid'] = $facility_id ? "facility_{$facility_id}" : NULL;

      // Set the endpoint will be used during queue execution.
      // @todo Change this to whatever lighthouse creates for us.
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
      $facility->vet_center = $this->getParentOffice();
      $facility->website = $this->getFacilityUrl();
      $facility->operating_status = [
        'code' => strtoupper($this->statusToPush),
        'additional_info' => (strtoupper($this->statusToPush) != 'NORMAL') ? $this->additionalInfoToPush : NULL,
      ];
      $facility->geographical_identifier = $this->facilityNode->field_geographical_identifier->value;
      $addresses_field = $this->facilityNode->get('field_address');
      $facility->address = $this->getAddresses($addresses_field);
      $facility->geometry = $this->getGeometry();
      $facility->call_for_hours = $this->getCallForHours();
      if (!$facility->call_for_hours) {
        $facility->hours = $this->getServiceHours($this->facilityNode->field_office_hours->getValue());
      }
      else {
        $facility->hours = NULL;
      }

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
   * Gets the classification of the facility.
   *
   * @return string|null
   *   The Facility API classification.
   */
  protected function getFacilityClassification() {
    $bundle = $this->facilityNode->bundle();
    return $this->facilitiesSourcedFromCms[$bundle]['classification'] ?? NULL;
  }

  /**
   * Get the parent office payload.
   *
   * @return object|null
   *   Stdclass object or parent properties or NULL if no parent.
   */
  protected function getParentOffice() {
    $parent = NULL;
    if ($this->facilityNode->hasField('field_office')) {
      $referenced_entities = $this->facilityNode->get('field_office')->referencedEntities();
      if (isset($referenced_entities[0])) {
        $parentFacility = $referenced_entities[0];
        $parent = new \stdClass();
        $parent->name = $parentFacility->getTitle();
        $url = $parentFacility->toUrl()->toString();
        $parent->url = "https://www.va.gov{$url}";
        $parent->phone = $parentFacility->get('field_phone_number')->value;
      }
    }
    return $parent;
  }

  /**
   * Get the value of call for hours field.
   *
   * @return bool|null
   *   Boolean if the field has values.  NULL if no value.
   */
  protected function getCallForHours() {
    // TRUE Veterans should call main Vet Center for hours.
    // FALSE Provide CAP hours online.
    if ($this->facilityNode->hasField('field_vetcenter_cap_hours_opt_in')) {
      return !(bool) $this->facilityNode->get('field_vetcenter_cap_hours_opt_in')->value;
    }
    return NULL;
  }

  /**
   * Gets an entry for the geolocation.
   *
   * @return object|null
   *   The type point and coordinates or NULL if undefined.
   */
  protected function getGeometry() {
    $geolocation = NULL;
    if ($this->facilityNode->hasField('field_geolocation')) {
      $geolocation = new \stdClass();
      $geolocation->type = $this->facilityNode->get('field_geolocation')->geo_type;
      $geolocation->coordinates = [
        $this->facilityNode->get('field_geolocation')->lon,
        $this->facilityNode->get('field_geolocation')->lat,
      ];
    }
    return $geolocation;
  }

  /**
   * Builds the appropriate url for a facility.
   *
   * @return string|null
   *   A facility page where this facility can be found.
   *   Null if something completely went wrong.
   */
  protected function getFacilityUrl(): string | null {
    $facility_url = NULL;
    $bundle = $this->facilityNode->bundle();
    if ($bundle === 'vet_center_cap') {
      // CAPs don't have their own page, so link to the parent facility's
      // locations page. Should look like this:
      // https://www.va.gov/big-vet-center/locations/#name-vet-center
      $facility_url = $this->getParentLocationsPageUrl($this->facilityNode);
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
  protected function isFacilitySourcedFromCms(NodeInterface $entity) : bool {
    return array_key_exists($entity->bundle(), $this->facilitiesSourcedFromCms);
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
    $defaultRevisionIsPublished = $defaultRevision->isPublished();
    // Forcing this to be TRUE since there are too many fields on a CAP to
    // to track and if anthing changed we would want to push.  So push with
    // any and all published revisions.
    $somethingChanged = TRUE;

    // Case race. First to evaluate to TRUE wins.
    switch (TRUE) {
      case $forcePush && $thisRevisionIsPublished:
        // Forced push from updates to referenced entity.
      case $thisRevisionIsPublished && $somethingChanged && $moderationState === self::STATE_PUBLISHED:
        // This revision is published and had a change, should be pushed.
      case $isArchived && $somethingChanged:
        // This node has been archived, got to push to remove it.
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
   * Removes values from anything that should not be kept in state.
   */
  protected function nuke() : void {
    $this->statusToPush = NULL;
    $this->additionalInfoToPush = NULL;
    unset($this->facilityNode);
  }

}
