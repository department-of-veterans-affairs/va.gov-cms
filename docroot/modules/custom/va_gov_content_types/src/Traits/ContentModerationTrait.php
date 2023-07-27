<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException;
use Drupal\va_gov_content_types\Interfaces\ContentModerationInterface;

/**
 * Provides a trait for some helpers for content moderation.
 */
trait ContentModerationTrait {

  /**
   * {@inheritDoc}
   */
  abstract public function hasField($fieldName);

  /**
   * {@inheritDoc}
   */
  abstract public function get($fieldName);

  /**
   * Indicate whether this node is moderated.
   *
   * @return bool
   *   TRUE if this node is moderated, FALSE otherwise.
   */
  public function isModerated(): bool {
    // The following is a better way to do this, but I'd prefer to not do this
    // until dependency injection is available in bundle classes.
    // @codingStandardsIgnoreStart
    /* 
    $moderation_information = \Drupal::service('content_moderation.moderation_information');
    return $moderation_information->shouldModerateEntitiesOfBundle($this->getEntityType(), $this->bundle());
     */
    // @codingStandardsIgnoreEnd
    // The following works well enough.
    return $this->hasField('moderation_state') && $this->get('moderation_state')->value !== NULL;
  }

  /**
   * Get the current moderation_state value.
   *
   * @return string
   *   The current moderation_state value.
   *
   * @throws \Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException
   *   Thrown when the node is not under content moderation.
   */
  public function getModerationState(): string {
    if (!$this->isModerated()) {
      throw new UnmoderatedContentTypeException('This node is not under content moderation.');
    }
    return $this->get('moderation_state')->value;
  }

  /**
   * Is this node currently published?
   *
   * Note that this uses the content moderation state, not the node status.
   *
   * @return bool
   *   TRUE if the node is currently published, FALSE otherwise.
   */
  public function isCmPublished(): bool {
    return $this->getModerationState() === ContentModerationInterface::MODERATION_STATE_PUBLISHED;
  }

  /**
   * Is this node currently archived?
   *
   * @return bool
   *   TRUE if the node is currently archived, FALSE otherwise.
   */
  public function isArchived(): bool {
    return $this->getModerationState() === ContentModerationInterface::MODERATION_STATE_ARCHIVED;
  }

  /**
   * Is this node currently a draft?
   *
   * @return bool
   *   TRUE if the node is currently a draft, FALSE otherwise.
   */
  public function isDraft(): bool {
    return $this->getModerationState() === ContentModerationInterface::MODERATION_STATE_DRAFT;
  }

}
