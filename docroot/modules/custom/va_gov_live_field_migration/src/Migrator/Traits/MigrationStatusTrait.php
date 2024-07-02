<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface;
use Drupal\va_gov_live_field_migration\State\StateInterface;

/**
 * Separate out database migration-specific methods.
 */
trait MigrationStatusTrait {

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
   * Get the state service.
   *
   * @return \Drupal\va_gov_live_field_migration\State\StateInterface
   *   The state service.
   */
  abstract protected function getState(): StateInterface;

  /**
   * {@inheritDoc}
   */
  public function getMigrationStatusObject(): StatusInterface {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $status = $this->getState()->getStatus($this->getPluginId(), $entityType, $fieldName);
    if (!$status) {
      $status = $this->getState()->createStatus($this->getPluginId(), $entityType, $fieldName);
    }
    return $status;
  }

  /**
   * {@inheritDoc}
   */
  public function setMigrationStatusObject(StatusInterface $status): void {
    $this->getState()->setStatus($status);
  }

  /**
   * {@inheritDoc}
   */
  public function deleteMigrationStatusObject(): void {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $this->getState()->deleteStatus($this->getPluginId(), $entityType, $fieldName);
  }

  /**
   * {@inheritDoc}
   */
  public function getMigrationStatus(): string {
    return $this->getMigrationStatusObject()->getStatus();
  }

  /**
   * {@inheritDoc}
   */
  public function updateMigrationStatus(string $status): void {
    $statusObject = $this->getMigrationStatusObject();
    $statusObject->setStatus($status);
    $this->setMigrationStatusObject($statusObject);
  }

  /**
   * {@inheritDoc}
   */
  public function deleteMigrationStatus(): void {
    $this->deleteMigrationStatusObject();
  }

}
