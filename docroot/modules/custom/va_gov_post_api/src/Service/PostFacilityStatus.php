<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_facilities\FacilityOps;
use Drupal\va_gov_lovell\LovellOps;

/**
 * Class PostFacilityService posts facility status info to Lighthouse.
 */
class PostFacilityStatus extends PostFacilityBase implements PostServiceInterface {

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
   * Adds facility service data to Post API queue.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param bool $forcePush
   *   Force processing of facility status if true.
   *
   * @return int
   *   The count of the number of items queued (1,0).
   */
  public function queueFacilityStatus(EntityInterface $entity, bool $forcePush = FALSE) {
    $queued_count = 0;
    // Vet Center CAPs are excluded from this push and will have their own.
    $is_vet_center_cap = $entity->bundle() === 'vet_center_cap';

    if ($entity instanceof NodeInterface && FacilityOps::isFacilityWithStatus($entity) && !$is_vet_center_cap) {
      /** @var \Drupal\node\NodeInterface $entity*/
      $this->facilityNode = $entity;
      $facility_id = $entity->hasField('field_facility_locator_api_id') ? $entity->get('field_facility_locator_api_id')->value : NULL;
      $data['nid'] = $entity->id();
      // Queue item's Unique ID.
      $data['uid'] = $facility_id ? "facility_status_{$facility_id}" : NULL;

      // Set a key based on which the endpoint will be
      // defined during queue execution.
      $data['endpoint_path'] = ($facility_id) ? "/services/va_facilities/v1/facilities/{$facility_id}/cms-overlay" : NULL;

      // Set payload. Default payload provided by this module is empty.
      // See README.md
      // Entity fields (updated and original) can be compared and processed in
      // order to structure the payload array.
      $data['payload'] = $this->getPayload($forcePush);

      // Only add to queue if payload is not empty.
      // If its empty, it means that there is no new information to send to
      // endpoint.
      if (!empty($data['payload']) && $facility_id) {
        $this->postQueue->addToQueue($data, $this->shouldDedupe());
        $queued_count = 1;
        $message = $this->t('The facility status info for %content_type: %facility_name is being sent to the Facility Locator.', $this->getMessageVars());
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
   * Add facility data to Post API queue by VAMC system.
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
    && ($entity->bundle() === 'health_care_region_page')
    && ($this->shouldPushSystem($entity))) {
      // Find all VAMC Facilities referencing this VAMC system node.
      $query = $this->entityTypeManager->getStorage('node')->getQuery();
      $nids = $query->condition('type', 'health_care_local_facility')
        ->condition('field_region_page', $entity->id())
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->execute();

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
   *   An array of message values keyed by repacement token.
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
      // When facilities are newly created from migration, they have no status.
      return $payload;
    }

    if (self::shouldPush($this->facilityNode, $forcePush)) {
      $payload = [
        'core' => [
          'facility_url' => $this->getFacilityUrl(),
        ],
        'operating_status' => [
          'code' => strtoupper($this->statusToPush),
          'additional_info' => (strtoupper($this->statusToPush) != 'NORMAL') ? $this->additionalInfoToPush : NULL,
        ],
      ];
      if ($this->getFacilityMentalHealthPhone()) {
        $payload['core']['mental_health_phone'] = $this->getFacilityMentalHealthPhone();
      }

      $this->addSupplementalStatus($payload);
      $this->getRelatedSystemInfo($payload);
      $this->getSystemOverrides($payload);
    }

    return $payload;
  }

  /**
   * Builds the appropriate url for a facility.
   *
   * @return string|null
   *   A facility locator detail page if the node is not published.
   *   A html page URL if the node is published.
   *   Null if something completely went wrong.
   */
  protected function getFacilityUrl(): string | null {
    $facility_url = NULL;
    if ($this->facilityNode->isPublished()) {
      if (!FacilityOps::facilityHasFePage($this->facilityNode)) {
        // This facility is published, but has no page of its own.
        $facility_url = $this->getParentLocationsPageUrl($this->facilityNode);
      }
      else {
        // The node is published, so use the FE URL of the page.
        $facility_url = "https://www.va.gov{$this->facilityNode->toUrl()->toString()}/";
      }

    }
    else {
      // The page is not published, so send the Facility Locator Detail Page.
      $facility_id = $this->facilityNode->hasField('field_facility_locator_api_id') ? $this->facilityNode->get('field_facility_locator_api_id')->value : NULL;
      if (!FacilityOps::isFacilityLaunched($this->facilityNode)) {
        // This hasn't launched, and is not published, so we don't know the url.
        $facility_url = NULL;
      }
      elseif ($facility_id) {
        // Has launched but the page does not exist yet so send locator detail.
        $facility_url = "https://www.va.gov/find-locations/facility/{$facility_id}";
      }
    }
    return $facility_url;
  }

  /**
   * Update payload array with supplemental status.
   *
   * @param array $payload
   *   Payload array.
   */
  protected function addSupplementalStatus(array &$payload) {
    // If this facility includes a supplemental status.
    if ($this->facilityNode->hasField('field_supplemental_status')) {
      $termId = $this->facilityNode->get('field_supplemental_status')->target_id;
      if (!is_null($termId)) {
        /** @var \Drupal\taxonomy\Entity\Term $term */
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId);
        $payload['operating_status']['supplemental_status'][] = [
          'id' => strtoupper($term->get('field_status_id')->value),
          'label' => $term->get('name')->value,
        ];
      }
    }
  }

  /**
   * Update payload array with related system information.
   *
   * @param array $payload
   *   Payload array.
   */
  protected function getRelatedSystemInfo(array &$payload) {
    // If this facility references a system, include system information.
    if ($this->facilityNode->hasField('field_region_page')) {
      $systemId = $this->facilityNode->get('field_region_page')->target_id;
      if (!is_null($systemId)) {
        /** @var \Drupal\node\NodeInterface $systemNode */
        $systemNode = $this->entityTypeManager->getStorage('node')->load($systemId);
        $systemUrl = $systemNode->toUrl()->toString();
        $payload['system'] = [
          'name' => $systemNode->get('title')->value,
          'url' => 'https://www.va.gov' . $systemUrl,
          'va_health_connect_phone' => $systemNode->get('field_va_health_connect_phone')->value,
        ];
      }
    }
  }

  /**
   * Update payload array with overrides for specific systems.
   *
   * @param array $payload
   *   Payload array.
   */
  protected function getSystemOverrides(array &$payload) {
    // If this facility references a system, include system information.
    if ($this->facilityNode->hasField('field_region_page')) {
      $systemId = $this->facilityNode->get('field_region_page')->target_id;
      // System url overrides for Lovell VA.
      if (($systemId === LovellOps::LOVELL_FEDERAL_SYSTEM_ID) || ($systemId === LovellOps::VA_SYSTEM_ID)) {
        $payload['core']['facility_url'] = 'https://www.va.gov/lovell-federal-health-care-va/';
        $payload['system']['url'] = 'https://www.va.gov/lovell-federal-health-care-va/';
        $payload['system']['covid_url'] = 'https://www.va.gov/lovell-federal-health-care-va/programs/covid-19-vaccines-and-testing/';
      }
      // Facility url overrides.
      $facility_id = $this->facilityNode->hasField('field_facility_locator_api_id') ? $this->facilityNode->get('field_facility_locator_api_id')->value : NULL;

      // Manila VA Clinic - vha_358.  Manila is a one facility system.
      if ($facility_id === 'vha_358') {
        $payload['core']['facility_url'] = 'https://www.visn21.va.gov/locations/manila.asp';
      }
    }
  }

  /**
   * Determines if the entity is such that it is covered by this service.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity (usually a node, but not required).
   *
   * @return bool
   *   TRUE if a pushable entity, FALSE otherwise.
   */
  public static function isPushAble(EntityInterface $entity): bool {
    if (
      $entity instanceof NodeInterface
      && FacilityOps::isFacilityWithStatus($entity)
      && $entity->bundle() !== 'vet_center_cap') {
      // CAPS do have status but are covered by a different push.
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Determines if the data says a payload should be assembled and pushed.
   *
   * Potentially alters the pushed data based on what it evaluates.  This is
   * beyond its concern but can not be separated cleanly.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity - A node in this case.
   * @param bool $forcePush
   *   Force processing of facility status if true.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  public static function shouldPush(EntityInterface $entity, bool $forcePush = FALSE) {
    if (!self::isPushable($entity)) {
      // This is not covered by this service.
      return FALSE;
    }
    else {
      // If it made it this far it is a node.
      /**@var \Drupal\node\NodeInterface $nodeFacility */
      $nodeFacility = $entity;
    }

    // Moderation state of what is being saved.
    $moderationState = $nodeFacility->get('moderation_state')->value;
    $isArchived = ($moderationState === self::STATE_ARCHIVED) ? TRUE : FALSE;
    $thisRevisionIsPublished = $nodeFacility->isPublished();
    $defaultRevision = self::getDefaultRevision($nodeFacility);
    $isNew = $nodeFacility->isNew();
    $defaultRevisionIsPublished = $defaultRevision->isPublished();

    // Case race. First to evaluate to TRUE wins.
    switch (TRUE) {
      case LovellOps::isLovellTricareSection($nodeFacility):
        // Node is part of the Lovell-Tricare section, do not push.
      case $nodeFacility->bundle() === 'vet_center_cap':
        // Vet Center Caps are handled by a separate push.
        $push = FALSE;
        break;

      case $forcePush && $thisRevisionIsPublished:
        // Forced push from updates to referenced entity.
      case $isNew:
        // A new node, should be pushed to initiate the value.
      case $thisRevisionIsPublished && $moderationState === self::STATE_PUBLISHED:
        // This revision is published, should be pushed.
      case $isArchived:
        // This node has been archived, got to push to remove it.
      case (!$defaultRevisionIsPublished && !$thisRevisionIsPublished):
        // Draft on an unpublished node, should be pushed.
      case ($thisRevisionIsPublished && !$defaultRevisionIsPublished):
        // To be the source of truth for urls, we need to push url on newly
        // published, because we use different urls.
        $push = TRUE;
        break;

      case ($defaultRevisionIsPublished && !$thisRevisionIsPublished && $moderationState === self::STATE_DRAFT):
        // Draft revision on published node, should not push, even w/bypass.
        $push = FALSE;
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

    $push = FALSE;
    if ($thisRevisionIsPublished && $moderationState === self::STATE_PUBLISHED) {
      $push = TRUE;
    }
    return $push;
  }

  /**
   * Gathers the mental health phone number from the facility.
   *
   * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/15686
   *
   * @return string
   *   The mental health phone number.
   */
  protected function getFacilityMentalHealthPhone(): string {
    $mental_health_phone = $this->getFieldSafe('field_mental_health_phone');
    return $mental_health_phone;
  }

  /**
   * Retrieves a field and returns the value if there is one or empty string.
   *
   * @param string $field_name
   *   The field name to get.
   *
   * @return string
   *   The field value if it exists, empty string otherwise.
   */
  protected function getFieldSafe(string $field_name): string {
    if ($this->facilityNode->hasField($field_name) && (!empty($this->facilityNode->get($field_name)->value))) {
      $value = $this->facilityNode->get($field_name)->value;
    }
    return $value ?? '';
  }

}
