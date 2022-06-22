<?php

namespace Drupal\va_gov_build_trigger\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\node\NodeInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\va_gov_build_trigger\Service\BuildRequesterInterface;

/**
 * VA.gov Build Trigger Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * The BuildRequester service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   */
  protected $buildRequester;

  /**
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface $buildRequester
   *   The build front end service.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   The environment discovery service.
   */
  public function __construct(
    BuildRequesterInterface $buildRequester,
    EnvironmentDiscovery $environmentDiscovery
  ) {
    $this->buildRequester = $buildRequester;
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Since all of the subscribed events here use the same handler method, the
    // test for this event subscriber only exercises ENTITY_INSERT. If any of
    // these events need a different behavior in the future, be sure to update
    // the corresponding test accordingly. Depending on what you change, the
    // test may not fail, but you'll have code that isn't covered by tests.
    return [
      EntityHookEvents::ENTITY_INSERT => 'handleEntityEvent',
      EntityHookEvents::ENTITY_UPDATE => 'handleEntityEvent',
      EntityHookEvents::ENTITY_DELETE => 'handleEntityEvent',
    ];
  }

  /**
   * Handle entity insert, update, and delete events.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent $event
   *   The event object passed by the event dispatcher.
   */
  public function handleEntityEvent(AbstractEntityEvent $event) : void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->maybeTriggerBuild($entity);
    }
  }

  /**
   * Request a build if appropriate.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The affected node.
   */
  protected function maybeTriggerBuild(NodeInterface $node) : void {
    if (
      $this->shouldTrigger() &&
      $this->isTriggerableState($node) &&
      ($this->isTriggerableType($node) || $this->facilityChangedStatus($node))
    ) {
      $msg_vars = [
        '%link_to_node' => $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString(),
        '%nid' => $node->id(),
        '%type' => $node->getType(),
      ];
      $log_message = $this->t('A content release was triggered by a change to %type: %link_to_node (node%nid).', $msg_vars);
      $this->buildRequester->requestFrontendBuild((string) $log_message);
    }
  }

  /**
   * Check if we should trigger on content changes.
   *
   * @return bool
   *   Whether or not a frontend build should be triggered on content changes.
   */
  protected function shouldTrigger() : bool {
    return $this->environmentDiscovery->contentEditsShouldTriggerFrontendBuild();
  }

  /**
   * Check to see if this had a status or status info change.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   *
   * @return bool
   *   TRUE if there was a status related change, FALSE if there was not.
   */
  protected function facilityChangedStatus(NodeInterface $node) {
    $status_field = 'field_operating_status_facility';
    $status_info_field = 'field_operating_status_more_info';
    if ($this->isFacility($node) && $node->hasField($status_field)) {
      // This is a node that should be checked for status change.
      $has_status_related_change = $this->changedValue($status_field, $node) || $this->changedValue($status_info_field, $node);
      // If the old state was draft, we can not detect a previous status change,
      // so we will have release because we can not be sure.
      $oldstate_was_draft = $this->getOriginalFieldValue('moderation_state', $node) === 'draft';
      $oldstate_was_archived = $this->getOriginalFieldValue('moderation_state', $node) === 'archived';
      $archived_from_published = ($this->getOriginalFieldValue('moderation_state', $node) === 'published' && $node->get('moderation_state')->value === 'archived');
      if ($this->isTriggerableState($node) && ($has_status_related_change || $oldstate_was_draft || $oldstate_was_archived || $archived_from_published)) {
        // The status related info changed, so release.
        return TRUE;
      }
    }
    // Made it this far, nothing changed.
    return FALSE;
  }

  /**
   * Gets the previously saved value of a field.
   *
   * @param string $field_name
   *   The machine name of the field to get.
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   *
   * @return string
   *   The value of the field, or '' if not found.
   */
  protected function getOriginalFieldValue($field_name, NodeInterface $node) {
    $value = '';
    if (isset($node->original) && ($node->original instanceof NodeInterface)) {
      // There was a previous save.
      $value = $node->original->get($field_name)->value;
    }

    return $value;
  }

  /**
   * Checks if a node is content type that can trigger a content release.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if it is a content type that can trigger a build.  FALSE otherwise.
   */
  protected function isTriggerableType(NodeInterface $node): bool {
    $triggerable_content_types = [
      'banner',
      'full_width_banner_alert',
    ];
    return in_array($node->getType(), $triggerable_content_types);
  }

  /**
   * Checks if a node is content type that is a facility.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if it is a content type is a facility.  FALSE otherwise.
   */
  protected function isFacility(NodeInterface $node): bool {
    $facility_content_types = [
      'health_care_local_facility',
      // 'nca_facility',  // Not rendered on the FE yet.  Add it when it is.
      // 'vba_facility',  // Not rendered on the FE yet.  Add it when it is.
      'vet_center_cap',
      'vet_center_outstation',
      'vet_center',
    ];
    return in_array($node->getType(), $facility_content_types);
  }

  /**
   * Checks if a node has gone through a state change that warrants a release.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if state change needs a release.  FALSE otherwise.
   */
  protected function isTriggerableState(NodeInterface $node): bool {
    $moderation_state_new = $node->get('moderation_state')->value;
    $is_published = $node->isPublished();
    // If the current state is archived, isPublished lies to us because the save
    // just happened, so we have to look back in time.
    $was_published = (isset($node->original) && ($node->original instanceof NodeInterface)) ? $node->original->isPublished() : FALSE;
    $has_been_published = $is_published || $was_published;

    switch (TRUE) {
      case ($moderation_state_new === 'published'):
        // Normal publish of revision.
      case ($has_been_published && ($moderation_state_new === 'archived')):
        // Archive of published node.
      case ($is_published && ($moderation_state_new === NULL)):
        // Covers publishing of entity not governed by workbench moderation.
      case ($was_published && !$is_published && ($moderation_state_new === NULL)):
        // Covers unpublishing of entity not governed by workbench moderation.
        return TRUE;

      default:
        return FALSE;
    }
  }

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param string $field_name
   *   The machine name of the field to check on.
   * @param \Drupal\node\NodeInterface $node
   *   The node being altered.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedValue($field_name, NodeInterface $node): bool {
    $value = $node->get($field_name)->value;
    $original_value = $this->getOriginalFieldValue($field_name, $node);
    return $value !== $original_value;
  }

}
