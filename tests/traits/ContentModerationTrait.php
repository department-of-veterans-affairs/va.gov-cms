<?php

namespace Traits;

use Drupal\node\Entity\Node;

/**
 * Provides methods to support testing of content moderation workflows.
 *
 * This trait is meant to be used only by test classes.
 */
trait ContentModerationTrait {

  /**
   * Get moderation state for node.
   *
   * @param int $nid
   *   Existing node id.
   *
   * @return string
   *   String representing moderation state.
   */
  public function getModerationState($nid) {
    $entity = Node::load($nid);
    $moderation_state = $entity->get('moderation_state')->getString();

    return $moderation_state;
  }

  /**
   * Get status for node.
   *
   * @param int $nid
   *   Existing node id.
   *
   * @return int
   *   1 or 0
   */
  public function getNodeStatus($nid) {
    $entity = Node::load($nid);

    return $entity->isPublished();
  }

}
