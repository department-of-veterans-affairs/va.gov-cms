<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * Provides an interface for some helpers for content moderation transitions.
 */
interface ContentModerationTransitionsInterface {

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
  public function getOriginalModerationState(): string;

  /**
   * Was this node previously published?
   *
   * @return bool
   *   TRUE if the node was previously published, FALSE otherwise.
   */
  public function wasPublished(): bool;

  /**
   * Was this node previously archived?
   *
   * @return bool
   *   TRUE if the node was previously archived, FALSE otherwise.
   */
  public function wasArchived(): bool;

  /**
   * Was this node previously a draft?
   *
   * @return bool
   *   TRUE if the node was previously a draft, FALSE otherwise.
   */
  public function wasDraft(): bool;

  /**
   * Is this node published, or was it _just_ archived from published?
   *
   * @return bool
   *   TRUE if the node is published or just archived, FALSE otherwise.
   */
  public function isPublishedOrWasJustArchived(): bool;

  /**
   * Did this node transition from published to archived?
   *
   * @return bool
   *   TRUE if the node _was_ published and _is_ now archived, otherwise FALSE.
   */
  public function didTransitionFromPublishedToArchived(): bool;

}
