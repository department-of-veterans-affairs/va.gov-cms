<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Makes decisions about whether flags should be set, unset, etc.
 */
interface FlagDecisionsInterface {

  /**
   * Should we set the `edited` flag for this node and user?
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node this user has ostensibly edited.
   * @param \Drupal\user\UserInterface $user
   *   A user who has ostensibly edited a node.
   *
   * @return bool
   *   TRUE if the flag can and should be set, otherwise FALSE.
   */
  public function shouldSetEditedFlag(NodeInterface $node, UserInterface $user);

}
