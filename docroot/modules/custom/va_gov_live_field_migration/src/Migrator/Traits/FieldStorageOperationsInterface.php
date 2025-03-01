<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Separate out field-storage-operations-specific methods.
 */
interface FieldStorageOperationsInterface {

  /**
   * Verify that the specified field is suitable for migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationFieldNotFoundException
   *   If the field does not exist.
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationFieldWrongTypeException
   *   If the field is not the correct type.
   */
  public function verifyField(string $fieldType): void;

  /**
   * Get the field storage config.
   *
   * @return \Drupal\field\FieldStorageConfigInterface
   *   The field storage config.
   */
  public function getFieldStorageConfig(): FieldStorageConfigInterface;

  /**
   * Get the field storage config.
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The field storage config.
   */
  public function getFieldConfig(string $bundle): FieldConfigInterface;

  /**
   * Get the bundles for the field storage config.
   *
   * @return array
   *   An associative array of bundle machine names and labels.
   */
  public function getFieldBundles(): array;

  /**
   * Delete the field storage config.
   */
  public function deleteFieldStorageConfig(): void;

  /**
   * Create a field storage config.
   *
   * @param array $fieldStorageConfig
   *   The field storage config.
   */
  public function createFieldStorageConfig(array $fieldStorageConfig): void;

  /**
   * Create field configs from an array, keyed by bundle.
   *
   * @param array $fieldConfigs
   *   The field configs.
   */
  public function createFieldConfigs(array $fieldConfigs): void;

}
