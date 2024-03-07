<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\va_gov_live_field_migration\Database\DatabaseInterface;
use Drupal\va_gov_live_field_migration\Reporter\ReporterInterface;

/**
 * Separate out database migration-specific methods.
 */
trait DatabaseMigrationTrait {

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
   * Get the database service.
   *
   * @return \Drupal\va_gov_live_field_migration\Database\DatabaseInterface
   *   The database service.
   */
  abstract protected function getDatabase(): DatabaseInterface;

  /**
   * Get the reporter service.
   *
   * @return \Drupal\va_gov_live_field_migration\Reporter\ReporterInterface
   *   The reporter service.
   */
  abstract protected function getReporter(): ReporterInterface;

  /**
   * {@inheritDoc}
   */
  public function dropTable(string $table): void {
    $this->getReporter()->reportInfo("Dropping table {$table}...");
    $this->getDatabase()->dropTable($table);
  }

  /**
   * {@inheritDoc}
   */
  public function backupFieldTables(): void {
    $entityType = $this->getEntityType();
    $fieldName = $this->getFieldName();
    $this->getReporter()->reportInfo("Backing up tables for field {$fieldName} on entity {$entityType}...");
    $this->getDatabase()->backupPrimaryFieldTable($entityType, $fieldName);
    $this->getDatabase()->backupFieldRevisionTable($entityType, $fieldName);
  }

}
