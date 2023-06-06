<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_facilities\FacilityOps;

/**
 * Class PostFacilityService posts facility status info to Lighthouse.
 */
class PostFacilityWithoutStatus extends PostFacilityBase implements PostServiceInterface {

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
  public function queueFacility(EntityInterface $entity, bool $forcePush = FALSE) {
    $queued_count = 0;

    if ($entity instanceof NodeInterface && FacilityOps::isFacilityWithoutStatus($entity)) {
      /** @var \Drupal\node\NodeInterface $entity*/
      $this->facilityNode = $entity;
      $facility_id = $entity->hasField('field_facility_locator_api_id') ? $entity->get('field_facility_locator_api_id')->value : NULL;
      $data['nid'] = $entity->id();
      // Queue item's Unique ID.
      $data['uid'] = $facility_id ? "facility_status_{$facility_id}" : NULL;

      // Set a key based on which the endpoint will be
      // defined during queue execution.
      $data['endpoint_path'] = ($facility_id) ? "/services/va_facilities/v0/facilities/{$facility_id}/cms-overlay" : NULL;

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

    // Facilities without status, have no status, but Lighthouse requires.
    // So we need to hard set the values to normal status.
    $this->statusToPush = 'NORMAL';
    $this->additionalInfoToPush = NULL;

    if (self::shouldPush($this->facilityNode, $forcePush)) {
      $payload = [
        'core' => [
          'facility_url' => $this->getFacilityUrl(),
        ],
        'operating_status' => [
          'code' => strtoupper($this->statusToPush),
          'additional_info' => $this->additionalInfoToPush,
        ],
      ];

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
      // The page is not published.
      $facility_id = $this->facilityNode->hasField('field_facility_locator_api_id') ? $this->facilityNode->get('field_facility_locator_api_id')->value : NULL;
      if (!FacilityOps::isFacilityLaunched($this->facilityNode)) {
        // This hasn't launched, and is not published, so we don't know the url.
        // Lighthouse will have to use their url csv to figure it out.
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
   * Determines if the entity is such that it is covered by this service.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity (usually a node, but not required).
   *
   * @return bool
   *   TRUE if a pushable entity, FALSE otherwise.
   */
  public static function isPushAble(EntityInterface $entity): bool {
    if ($entity instanceof NodeInterface && FacilityOps::isFacilityWithoutStatus($entity)) {
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
      case $isArchived && $isNew:
        // Essentially an impossible combination. New and archived
        // But if it did occur, no harm in pushing.
      case $forcePush && $thisRevisionIsPublished:
        // Forced push from updates to referenced entity.
      case $isNew:
        // A new node, should be pushed to initiate the value.
      case $thisRevisionIsPublished && $moderationState === self::STATE_PUBLISHED:
        // This revision is published and should be pushed.
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

}
