<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * Provides an interface for some helpers for content moderation.
 */
interface ContentModerationInterface {

  const MODERATION_STATE_ARCHIVED = 'archived';
  const MODERATION_STATE_DRAFT = 'draft';
  const MODERATION_STATE_PUBLISHED = 'published';

  /**
   * Indicate whether this node is moderated.
   *
   * @return bool
   *   TRUE if this node is moderated, FALSE otherwise.
   */
  public function isModerated(): bool;

  /**
   * Get the current moderation_state value.
   *
   * @return string
   *   The current moderation_state value.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NotModeratedContentTypeException
   *   Thrown when the node is not under content moderation.
   */
  public function getModerationState(): string;

  /**
   * Is this node currently published?
   *
   * Note that this uses the content moderation state, not the node status.
   *
   * @return bool
   *   TRUE if the node is currently published, FALSE otherwise.
   */
  public function isCmPublished(): bool;

  /**
   * Is this node currently archived?
   *
   * @return bool
   *   TRUE if the node is currently archived, FALSE otherwise.
   */
  public function isArchived(): bool;

  /**
   * Is this node currently a draft?
   *
   * @return bool
   *   TRUE if the node is currently a draft, FALSE otherwise.
   */
  public function isDraft(): bool;

}
