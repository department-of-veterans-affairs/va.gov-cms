<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\va_gov_live_field_migration\Exception\MigrationFieldNotFoundException;
use Drupal\va_gov_live_field_migration\Exception\MigrationFieldWrongTypeException;

/**
 * Separate out field storage operations-specific methods.
 */
trait FieldStorageOperationsTrait {

  /**
   * Get the entity type.
   *
   * @return string
   *   The entity type.
   */
  abstract public function getEntityType(): string;

  /**
   * Get the field name.
   *
   * @return string
   *   The field name.
   */
  abstract public function getFieldName(): string;

  /**
   * Get the entity type manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager service.
   */
  abstract protected function getEntityTypeManager(): EntityTypeManagerInterface;

  /**
   * Get the entity type repository service.
   *
   * @return \Drupal\Core\Entity\EntityTypeRepositoryInterface
   *   The entity type repository service.
   */
  abstract protected function getEntityTypeRepository(): EntityTypeRepositoryInterface;

  /**
   * Get the entity field manager service.
   *
   * @return \Drupal\Core\Entity\EntityFieldManagerInterface
   *   The entity field manager service.
   */
  abstract protected function getEntityFieldManager(): EntityFieldManagerInterface;

  /**
   * {@inheritDoc}
   */
  public function verifyField(string $fieldType): void {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $this->reporter->reportInfo("Verifying field {$fieldName} on entity {$entityType}...");
    $fieldStorageDefinition = $this->getEntityFieldManager()->getFieldStorageDefinitions($entityType)[$fieldName];
    if ($fieldStorageDefinition == NULL) {
      throw new MigrationFieldNotFoundException("Field $fieldName not found on $entityType.");
    }
    if ($fieldStorageDefinition->getType() !== $fieldType) {
      throw new MigrationFieldWrongTypeException("Field $fieldName on $entityType is not of type $fieldType.");
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldStorageConfig(): FieldStorageConfigInterface {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $fieldStorageConfigStorage = $this->getEntityTypeManager()->getStorage('field_storage_config');
    $fieldStorageConfig = $fieldStorageConfigStorage->load($entityType . '.' . $fieldName);
    return $fieldStorageConfig;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldConfig(string $bundle): FieldConfigInterface {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $fieldConfigStorage = $this->getEntityTypeManager()->getStorage('field_config');
    $fieldConfig = $fieldConfigStorage->load($entityType . '.' . $bundle . '.' . $fieldName);
    return $fieldConfig;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldBundles(): array {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $fieldStorageConfig = $this->getFieldStorageConfig($entityType, $fieldName);
    return $fieldStorageConfig->getBundles();
  }

  /**
   * {@inheritDoc}
   */
  public function deleteFieldStorageConfig(): void {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $this->reporter->reportInfo("Deleting field storage config for field {$fieldName} on entity {$entityType}...");
    $fieldStorageConfig = $this->getFieldStorageConfig($entityType, $fieldName);
    $fieldStorageConfig->delete();
  }

  /**
   * {@inheritDoc}
   */
  public function createFieldStorageConfig(array $fieldStorageConfig): void {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $this->reporter->reportInfo("Creating field storage config for field {$fieldName} on entity {$entityType}...");
    $fieldStorageConfigStorage = $this->getEntityTypeManager()->getStorage('field_storage_config');
    $fieldStorageConfig = $fieldStorageConfigStorage->create($fieldStorageConfig);
    $fieldStorageConfig->save();
  }

  /**
   * {@inheritDoc}
   */
  public function createFieldConfigs(array $fieldConfigs): void {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $this->reporter->reportInfo("Creating field configs for field {$fieldName} on entity {$entityType}...");
    $fieldConfigStorage = $this->getEntityTypeManager()->getStorage('field_config');
    foreach ($fieldConfigs as $fieldConfig) {
      $fieldConfig = $fieldConfigStorage->create($fieldConfig);
      $fieldConfig->save();
    }
  }

}
