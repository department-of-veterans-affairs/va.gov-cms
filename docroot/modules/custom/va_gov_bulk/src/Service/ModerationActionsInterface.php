<?php

namespace Drupal\va_gov_bulk\Service;

use Drupal\node\NodeInterface;

/**
 * Encapsulates logic for bulk moderation actions.
 */
interface ModerationActionsInterface {

  /**
   * Archive the given node.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  public function archiveNode(NodeInterface $node) : NodeInterface;

  /**
   * Publish the latest revision of the given node.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  public function publishLatestRevision(NodeInterface $node) : NodeInterface;

}
