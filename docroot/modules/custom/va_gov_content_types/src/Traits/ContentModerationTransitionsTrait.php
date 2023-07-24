<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_types\Exception\NotModeratedContentTypeException;
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
  abstract public function getOriginal(): VaNodeInterface;

  /**
   * {@inheritDoc}
   */
  abstract public function isArchived(): bool;

  /**
   * Get the original moderation_state value.
   *
   * @return string
   *   The original moderation_state value.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NoOriginalExistsException
   *   Thrown when the node has no original version.
   * @throws \Drupal\va_gov_content_types\Exception\NotModeratedContentTypeException
   *   Thrown when the node is not under content moderation.
   */
  public function getOriginalModerationState(): string {
    /** @var \Drupal\va_gov_content_types\Interfaces\ContentModerationInterface $this */
    if (!$this->isModerated()) {
      throw new NotModeratedContentTypeException('This node is not under content moderation.');
    }
    /** @var \Drupal\va_gov_content_types\Interfaces\GetOriginalInterface $this */
    return $this->getOriginal()->getModerationState();
  }

  /**
   * Was this node previously published?
   *
   * @return bool
   *   TRUE if the node was previously published, FALSE otherwise.
   */
  public function wasPublished(): bool {
    return $this->getOriginalModerationState() === ContentModerationInterface::MODERATION_STATE_PUBLISHED;
  }

  /**
   * Was this node previously archived?
   *
   * @return bool
   *   TRUE if the node was previously archived, FALSE otherwise.
   */
  public function wasArchived(): bool {
    return $this->getOriginalModerationState() === ContentModerationInterface::MODERATION_STATE_ARCHIVED;
  }

  /**
   * Was this node previously a draft?
   *
   * @return bool
   *   TRUE if the node was previously a draft, FALSE otherwise.
   */
  public function wasDraft(): bool {
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
    // If the current state is archived, isPublished() lies to us because the
    // save just happened, so we have to look back in time.
    return $this->wasPublished() && $this->isArchived();
  }

}
