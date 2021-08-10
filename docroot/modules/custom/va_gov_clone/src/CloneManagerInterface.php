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
   * @param int $section_id
   *   The arguments passed from Drupal.
   *
   * @return int
   *   The total count of content updated.
   */
  public function cloneSection(int $section_id) : int;

  /**
   * Clone Entites.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   Entities to clone.
   */
  public function cloneEntities(array $entities) : void;

  /**
   * Clone a node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Entity to clone.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The cloned entity.
   */
  public function cloneEntity(EntityInterface $entity) : ?EntityInterface;

}
