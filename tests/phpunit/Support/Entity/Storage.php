<?php

namespace Tests\Support\Entity;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * A helper class for entity operations.
 */
class Storage {

  /**
   * Delete matching entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param array $conditions
   *   An associative array of field conditions.
   */
  public static function deleteMatchingEntities(string $entity_type, string $bundle, array $conditions = []) : void {
    $result = self::getMatchingEntities($entity_type, $bundle, $conditions);
    $storage = self::getEntityStorage($entity_type);
    $entities = $storage->loadMultiple($result);
    $storage->delete($entities);
  }

  /**
   * Get count of matching entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param array $conditions
   *   An associative array of field conditions.
   *
   * @return int
   *   The count of matching entities.
   */
  public static function getMatchingEntityCount(string $entity_type, string $bundle, array $conditions = []) : int {
    return count(self::getMatchingEntities($entity_type, $bundle, $conditions));
  }

  /**
   * Get the count of matching entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param array $conditions
   *   An associative array of field conditions.
   *
   * @return array
   *   The matching entities.
   */
  public static function getMatchingEntities(string $entity_type, string $bundle, array $conditions = []) : array {
    $storage = self::getEntityStorage($entity_type);
    $query = $storage->getQuery()->condition('type', $bundle);

    foreach ($conditions as $key => $value) {
      $query->condition($key, $value);
    }

    return $query->execute();
  }

  /**
   * Get entity type storage.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The entity storage.
   */
  protected static function getEntityStorage(string $entity_type) : EntityStorageInterface {
    /** @var \Drupal\Core\Entity\EntityTypeManager */
    $entityTypeManager = \Drupal::service('entity_type.manager');

    return $entityTypeManager->getStorage($entity_type);
  }

}
