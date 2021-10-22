<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;

/**
 * Class PostFacilityService posts facility status info to Lighthouse.
 */
class PostFacilityStatus extends PostFacilityBase {

  /**
   * A array of any errors in prepping the data.
   *
   * @var array
   */
  protected $errors = [];

  const STATE_ARCHIVED = 'archived';
  const STATE_DRAFT = 'draft';
  const STATE_PUBLISHED = 'published';

  /**
   * The facility service node's default revision.
   *
   * This is either the last published revision, or the last revision before
   * the current save.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $defaultRevision;

  /**
   * The facility service node.
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
   * The services that should be pushed to Lighthouse.
   *
   * For now we are only pushing covid 19 services. The key is only for
   * making sense of code, the TID is what is used for comparison.
   *
   * @var array
   */
  protected $facilitiesWithServices = [
    'health_care_local_facility',
    'nca_facility',
    'vba_facility',
    'vet_center',
    'vet_center_outstation',
  ];

  /**
   * Adds facility service data to Post API queue.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return int
   *   The count of the number of items queued (1,0).
   */
  public function queueFacilityStatus(EntityInterface $entity) {
    $queued_count = 0;

    if ($entity instanceof NodeInterface && $this->isFacilityWithStatus($entity)) {
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
      $data['payload'] = $this->getPayload();

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
   * @return array
   *   Payload array.
   */
  protected function getPayload(): array {
    // Default payload is an empty array.
    $payload = [];

    // Current field values.
    $this->statusToPush = $this->facilityNode->get('field_operating_status_facility')->value;
    $this->additionalInfoToPush = $this->facilityNode->get('field_operating_status_more_info')->value;

    if (empty($this->statusToPush)) {
      // We can not send this without a status, so bail out.
      // When facilities are newly created from migration, they have no status.
      return $payload;
    }

    if ($this->shouldPush()) {
      $payload = [
        'operating_status' => [
          'code' => strtoupper($this->statusToPush),
          'additional_info' => $this->additionalInfoToPush,
        ],
      ];
    }

    return $payload;
  }

  /**
   * Checks if the entity is a facility node with status info.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility with status info. FALSE otherwise.
   */
  protected function isFacilityWithStatus(NodeInterface $entity) : bool {
    return in_array($entity->bundle(), $this->facilitiesWithServices);
  }

  /**
   * Determines if the data says a payload should be assembled and pushed.
   *
   * Potentially alters the pushed data based on what it evaluates.  This is
   * beyond its concern but can not be separated cleanly.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  protected function shouldPush() {
    // Moderation state of what is being saved.
    $moderationState = $this->facilityNode->get('moderation_state')->value;
    $isArchived = ($moderationState === self::STATE_ARCHIVED) ? TRUE : FALSE;
    $thisRevisionIsPublished = $this->facilityNode->isPublished();
    $this->setDefaultRevision();
    $isNew = $this->facilityNode->isNew();
    $defaultRevisionIsPublished = $this->defaultRevision->isPublished();
    $statusChanged = $this->changedValue('field_operating_status_facility');
    $statusInfoChanged = $this->changedValue('field_operating_status_more_info');
    $somethingChanged = $statusChanged || $statusInfoChanged;

    // Case race. First to evaluate to TRUE wins.
    switch (TRUE) {
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
   * Decides what to use as the previous/default revision and sets it.
   */
  protected function setDefaultRevision() : void {
    $hasOriginal = isset($this->facilityNode->original) && ($this->facilityNode->original instanceof EntityInterface);
    if ($this->facilityNode->isNew()) {
      // There is no previous revision but to make comparison easier, set
      // the current node as the default.
      $this->defaultRevision = $this->facilityNode;
    }
    elseif (($this->facilityNode->get('moderation_state')->value === self::STATE_PUBLISHED || !$this->facilityNode->isPublished()) && $hasOriginal) {
      // If it has never been published we just want the last save.
      // If the node is published, loading the default is an exact copy,
      // because the save already happened. Switch to using original.
      $this->defaultRevision = $this->facilityNode->original;
    }
    else {
      $this->defaultRevision = $this->entityTypeManager->getStorage('node')->load($this->facilityNode->id());
    }
  }

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param string $field_name
   *   The machine name of the field to check on.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedValue($field_name): bool {
    $value = $this->facilityNode->get($field_name)->value;
    $original_value = $this->defaultRevision->get($field_name)->value;

    return $value !== $original_value;
  }

  /**
   * Removes values from anything that should not be kept in state.
   */
  protected function nuke() : void {
    $this->statusToPush = NULL;
    $this->additionalInfoToPush = NULL;
    unset($this->defaultRevision);
    unset($this->facilityNode);
  }

}
