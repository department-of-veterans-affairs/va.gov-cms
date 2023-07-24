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

}
