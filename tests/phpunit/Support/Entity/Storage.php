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
   * @param string $bundle_key
   *   The entity bundle key.
   */
  public static function deleteMatchingEntities(string $entity_type, string $bundle, array $conditions = [], string $bundle_key = 'type') : void {
    $result = self::getMatchingEntities($entity_type, $bundle, $conditions, $bundle_key);
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
   *   The entity bundle, defaults to empty string. Some entities do not have
   *   bundles.
   * @param array $conditions
   *   An associative array of field conditions.
   * @param string $bundle_key
   *   The entity bundle key.
   *
   * @return int
   *   The count of matching entities.
   */
  public static function getMatchingEntityCount(string $entity_type, string $bundle = '', array $conditions = [], string $bundle_key = 'type') : int {
    return count(self::getMatchingEntities($entity_type, $bundle, $conditions, $bundle_key));
  }

  /**
   * Get the count of matching entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The entity bundle, defaults to empty string. Some entities do not have
   *   bundles.
   * @param array $conditions
   *   An associative array of field conditions.
   * @param string $bundle_key
   *   The entity bundle key.
   *
   * @return array
   *   The matching entities.
   */
  public static function getMatchingEntities(string $entity_type, string $bundle = '', array $conditions = [], string $bundle_key = 'type') : array {
    $storage = self::getEntityStorage($entity_type);
    $query = $storage->getQuery();
    if (!empty($bundle)) {
      $query->condition($bundle_key, $bundle);
    }

    foreach ($conditions as $key => $value) {
      $query->condition($key, $value);
    }

    return $query->accessCheck(FALSE)->execute();
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
