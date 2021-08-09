<?php

namespace Drupal\va_gov_clone;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface to the Clone Manager.
 *
 * This class will be used to clone content in a controlled way.
 */
interface CloneManagerInterface {

  /**
   * Clone All items.
   *
   * @param int $office_tid
   *   The arguments passed from Drupal.
   *
   * @return int
   *   The total count of content updated.
   */
  public function cloneAll(int $office_tid) : int;

  /**
   * Clone nodes.
   *
   * @param \Drupal\node\NodeInterface[] $nodes
   *   Nodes to clone.
   */
  public function cloneEntities(array $nodes) : void;

  /**
   * Clone a node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The Node to clone.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The cloned node.
   */
  public function cloneEntity(EntityInterface $node) : ?EntityInterface;

}
