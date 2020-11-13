<?php

namespace Drupal\va_gov_bulk\Service;

use Drupal\node\NodeInterface;

/**
 * Interface ModerationActionsInterface.
 */
interface ModerationActionsInterface {

  /**
   * Unpublish the current revision of the given node.
   *
   * @return Drupal\node\NodeInterface
   *   The node.
   */
  public function unpublishCurrentRevision(NodeInterface $node) : NodeInterface;

  /**
   * Publish the latest revision of the given node.
   *
   * @return Drupal\node\NodeInterface
   *   The node.
   */
  public function publishLatestRevision(NodeInterface $node) : NodeInterface;

}
