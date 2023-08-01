<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * Provides an interface for triggering a content release event.
 */
interface ContentReleaseTriggerInterface {

  const ALWAYS_TRIGGERING_TYPES = [
    'banner',
    'full_width_banner_alert',
  ];

  // Methods that will be used to determine if a content release should be
  // triggered.
  const CONTENT_RELEASE_DETAILS = [
    'isFacility',
    'isModerated',
    'hasOriginal',
    'didChangeOperatingStatus',
    'alwaysTriggersContentRelease',
    'isModeratedAndPublished',
    'isModeratedAndTransitionedFromPublishedToArchived',
    'isUnmoderatedAndPublished',
    'isUnmoderatedAndWasPreviouslyPublished',
    'didTransitionFromPublishedToArchived',
    'isCmPublished',
    'isPublished',
    'isArchived',
    'isDraft',
    'wasPublished',
  ];

  /**
   * Indicate whether this node should trigger a content release event.
   *
   * The decision is made based on a number of factors:
   * - content type
   * - moderation state
   * - previous moderation state
   * - where changes occurred
   * etc.
   *
   * @return bool
   *   TRUE if this node should trigger a content release event, or
   *   FALSE otherwise.
   */
  public function shouldTriggerContentRelease(): bool;

  /**
   * Indicate whether this content type inherently triggers when modified.
   *
   * @return bool
   *   TRUE if this content type inherently triggers when modified, or
   *   FALSE otherwise.
   */
  public function alwaysTriggersContentRelease(): bool;

  /**
   * Checks if a node has gone through a state change that warrants a release.
   *
   * @return bool
   *   TRUE if state change needs a release.  FALSE otherwise.
   */
  public function hasTriggeringChanges(): bool;

  /**
   * Is this node moderated and published?
   *
   * @return bool
   *   TRUE if this node is moderated and published, or
   *   FALSE otherwise.
   */
  public function isModeratedAndPublished(): bool;

  /**
   * Is this node moderated, and did it transition from published to archived?
   *
   * @return bool
   *   TRUE if this node is moderated and transitioned from published to
   *   archived, or FALSE otherwise.
   */
  public function isModeratedAndTransitionedFromPublishedToArchived(): bool;

  /**
   * Is this node unmoderated and currently published?
   *
   * @return bool
   *   TRUE if this node is unmoderated and currently published, or
   *   FALSE otherwise.
   */
  public function isUnmoderatedAndPublished(): bool;

  /**
   * Is this node unmoderated but was previously published?
   *
   * @return bool
   *   TRUE if this node is unmoderated but was previously published, or
   *   FALSE otherwise.
   */
  public function isUnmoderatedAndWasPreviouslyPublished(): bool;

  /**
   * Get details about the node and changes thereto.
   *
   * @return array
   *   The details, as an associative array.
   */
  public function getContentReleaseTriggerDetails(): array;

}
