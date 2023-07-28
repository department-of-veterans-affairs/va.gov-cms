<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Entity\VaNodeInterface;
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
   * {@inheritDoc}
   */
  abstract public function getOriginal(): VaNodeInterface;

  /**
   * {@inheritDoc}
   */
  abstract public function hasOriginal(): bool;

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
    switch (TRUE) {
      case $this->isModeratedAndPublished():
      case $this->isModeratedAndTransitionedFromPublishedToArchived():
      case $this->isUnmoderatedAndPublished():
      case $this->isUnmoderatedAndWasPreviouslyPublished():
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

  /**
   * Is this node moderated and published?
   *
   * @return bool
   *   TRUE if this node is moderated and published, or
   *   FALSE otherwise.
   */
  public function isModeratedAndPublished(): bool {
    return $this->isModerated() && $this->isCmPublished();
  }

  /**
   * Is this node moderated, and did it transition from published to archived?
   *
   * @return bool
   *   TRUE if this node is moderated and transitioned from published to
   *   archived, or FALSE otherwise.
   */
  public function isModeratedAndTransitionedFromPublishedToArchived(): bool {
    return $this->isModerated() && $this->didTransitionFromPublishedToArchived();
  }

  /**
   * Is this node unmoderated and currently published?
   *
   * @return bool
   *   TRUE if this node is unmoderated and currently published, or
   *   FALSE otherwise.
   */
  public function isUnmoderatedAndPublished(): bool {
    return !$this->isModerated() && $this->isPublished();
  }

  /**
   * Is this node unmoderated but was previously published?
   *
   * @return bool
   *   TRUE if this node is unmoderated but was previously published, or
   *   FALSE otherwise.
   */
  public function isUnmoderatedAndWasPreviouslyPublished(): bool {
    if ($this->isModerated()) {
      return FALSE;
    }
    return $this->hasOriginal() && $this->getOriginal()->isPublished();
  }

  /**
   * Safely get a content release detail about the node and its changes.
   *
   * @param callable $callback
   *   The callback to invoke.
   * @param mixed $default
   *   The default value to return if the callback throws an exception.
   *
   * @return mixed
   *   The value returned by the callback, or the default value if the callback
   *   threw an exception.
   */
  public function safelyGetContentReleaseDetail(callable $callback, $default = NULL) {
    try {
      return $callback($this);
    }
    catch (\Throwable $exception) {
      return $default;
    }
  }

  /**
   * Get details about the node and changes thereto.
   *
   * @return array
   *   The details, as an associative array.
   */
  public function getContentReleaseTriggerDetails(): array {
    $details = ContentReleaseTriggerInterface::CONTENT_RELEASE_DETAILS;
    $result = [];
    foreach ($details as $detail) {
      $result[$detail] = $this->safelyGetContentReleaseDetail(function ($node) use ($detail) {
        return call_user_func([$node, $detail]);
      }, 'Not Applicable');
    }
    return $result;
  }

}
