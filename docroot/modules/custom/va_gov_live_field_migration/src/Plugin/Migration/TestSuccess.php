<?php

namespace Drupal\va_gov_live_field_migration\Plugin\Migration;

use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginBase;

/**
 * Success test migration.
 *
 * This always succeeds immediately.
 *
 * @Migration(
 *   id = "test_success",
 *   label = @Translation("Success Test")
 * )
 */
class TestSuccess extends MigrationPluginBase {

  /**
   * {@inheritDoc}
   */
  public function runMigration(string $entityType, string $fieldName) : void {
    $this->reporter->reportInfo("Successfully completed a migration for $entityType $fieldName.");
  }

  /**
   * {@inheritDoc}
   */
  public function rollbackMigration(string $entityType, string $fieldName) : void {
    $this->reporter->reportInfo("Successfully rolled back a migration for $entityType $fieldName.");
  }

  /**
   * {@inheritDoc}
   */
  public function verifyMigration(string $entityType, string $fieldName) : void {
    $this->reporter->reportInfo("Successfully verified back a migration for $entityType $fieldName.");
  }

}
