<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Makes decisions about whether flags should be set, unset, etc.
 */
interface FlaggingInterface {

  /**
   * Set the `edited` flag for this node and user, if appropriate.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node this user has edited.
   * @param \Drupal\user\UserInterface $user
   *   A user who has edited a node.
   */
  public function setEditedFlag(NodeInterface $node, UserInterface $user): void;

}
