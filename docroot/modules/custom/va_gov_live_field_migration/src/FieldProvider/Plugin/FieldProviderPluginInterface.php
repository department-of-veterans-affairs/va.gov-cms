<?php

namespace Drupal\va_gov_live_field_migration\FieldProvider\Plugin;

/**
 * Interface used for FieldProvider plugins.
 */
interface FieldProviderPluginInterface {

  /**
   * Return a list of fields to migrate for the specified entity type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string|null $bundle
   *   An optional bundle to filter by.
   *
   * @return array
   *   An indexed array of fields to index.
   */
  public function getFields(string $entityType, string $bundle = NULL): array;

}
