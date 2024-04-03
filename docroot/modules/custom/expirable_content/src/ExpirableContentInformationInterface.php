<?php

declare(strict_types = 1);

namespace Drupal\expirable_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Interface for the expirable_content.information service.
 */
interface ExpirableContentInformationInterface {

  /**
   * Determines if an entity is expirable.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if an entity is expirable, FALSE otherwise.
   */
  public function isExpirableEntity(EntityInterface $entity): bool;

  /**
   * Determines if any bundles of a given entity type are expirable.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type to check.
   *
   * @return bool
   *   TRUE if any bundle of the provided entity type is expirable.
   */
  public function isExpirableEntityType(EntityTypeInterface $entityType): bool;

}
