<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Interfaces\ContentReleaseTriggerInterface;

/**
 * Provides a trait for triggering a content release event.
 */
trait ContentReleaseTriggerTrait {

  /**
   * {@inheritDoc}
   */
  abstract public function getType();

  /**
   * {@inheritDoc}
   */
  abstract public function isFacility(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function didChangeOperatingStatus(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function isModerated(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function isCmPublished(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function didTransitionFromPublishedToArchived(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function isPublished();

  /**
   * {@inheritDoc}
   */
  abstract public function wasPublished(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function isArchived(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function isDraft(): bool;

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
  public function shouldTriggerContentRelease(): bool {
    if (!$this->hasTriggeringChanges()) {
      return FALSE;
    }
    if ($this->alwaysTriggersContentRelease()) {
      return TRUE;
    }
    if (!$this->isFacility()) {
      return FALSE;
    }
    return $this->didChangeOperatingStatus();
  }

  /**
   * Checks if a node has gone through a state change that warrants a release.
   *
   * @return bool
   *   TRUE if state change needs a release.  FALSE otherwise.
   */
  public function hasTriggeringChanges(): bool {
    $isModerated = $this->isModerated();
    switch (TRUE) {
      case $isModerated && $this->isCmPublished():
        // If the node is currently published under content moderation...
      case $isModerated && $this->didTransitionFromPublishedToArchived():
        // If we archived a published node...
      case !$isModerated && $this->isPublished():
        // If we published a node that is not under content moderation...
      case !$isModerated && !$this->isPublished() && $this->wasPublished():
        // If we unpublished a node that is not under content moderation...
        return TRUE;

      default:
        // Otherwise, the state change does not warrant a release.
        return FALSE;
    }
  }

  /**
   * Indicate whether this content type inherently triggers when modified.
   *
   * @return bool
   *   TRUE if this content type inherently triggers when modified, or
   *   FALSE otherwise.
   */
  public function alwaysTriggersContentRelease(): bool {
    return in_array($this->getType(), ContentReleaseTriggerInterface::ALWAYS_TRIGGERING_TYPES);
  }

}
