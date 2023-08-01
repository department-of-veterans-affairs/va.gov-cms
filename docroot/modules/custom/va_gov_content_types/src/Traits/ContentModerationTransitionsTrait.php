<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_types\Exception\NoOriginalExistsException;
use Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException;
use Drupal\va_gov_content_types\Interfaces\ContentModerationInterface;

/**
 * Provides a trait for some helpers for content moderation transitions.
 */
trait ContentModerationTransitionsTrait {

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
  abstract public function hasOriginal(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function getOriginal(): VaNodeInterface;

  /**
   * {@inheritDoc}
   */
  abstract public function isArchived(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function get($fieldName);

  /**
   * Get the original moderation_state value.
   *
   * @return string
   *   The original moderation_state value.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NoOriginalExistsException
   *   Thrown when the node has no original version.
   * @throws \Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException
   *   Thrown when the node is not under content moderation.
   */
  public function getOriginalModerationState(): string {
    if (!$this->isModerated()) {
      throw new UnmoderatedContentTypeException('This node is not under content moderation.');
    }
    if (!$this->hasOriginal()) {
      throw new NoOriginalExistsException('This node does not have an original version.');
    }
    return $this->getOriginal()->getModerationState();
  }

  /**
   * Was this node previously published?
   *
   * @return bool
   *   TRUE if the node was previously published, FALSE otherwise.
   */
  public function wasPublished(): bool {
    if (!$this->hasOriginal()) {
      return FALSE;
    }
    return $this->getOriginalModerationState() === ContentModerationInterface::MODERATION_STATE_PUBLISHED;
  }

  /**
   * Was this node previously archived?
   *
   * @return bool
   *   TRUE if the node was previously archived, FALSE otherwise.
   */
  public function wasArchived(): bool {
    if (!$this->hasOriginal()) {
      return FALSE;
    }
    return $this->getOriginalModerationState() === ContentModerationInterface::MODERATION_STATE_ARCHIVED;
  }

  /**
   * Was this node previously a draft?
   *
   * @return bool
   *   TRUE if the node was previously a draft, FALSE otherwise.
   */
  public function wasDraft(): bool {
    if (!$this->hasOriginal()) {
      return FALSE;
    }
    return $this->getOriginalModerationState() === ContentModerationInterface::MODERATION_STATE_DRAFT;
  }

  /**
   * Is this node published, or was it _just_ archived from published?
   *
   * @return bool
   *   TRUE if the node is published or just archived, FALSE otherwise.
   */
  public function isPublishedOrWasJustArchived(): bool {
    return $this->isCmPublished() || $this->didTransitionFromPublishedToArchived();
  }

  /**
   * Did this node transition from published to archived?
   *
   * @return bool
   *   TRUE if the node _was_ published and _is_ now archived, otherwise FALSE.
   */
  public function didTransitionFromPublishedToArchived(): bool {
    if (!$this->hasOriginal()) {
      return FALSE;
    }
    // If the current state is archived, isPublished() lies to us because the
    // save just happened, so we have to look back in time.
    return $this->wasPublished() && $this->isArchived();
  }

}
