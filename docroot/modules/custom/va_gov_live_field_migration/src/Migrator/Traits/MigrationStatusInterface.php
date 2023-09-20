<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface;

/**
 * Separate out migration-status-specific methods.
 */
interface MigrationStatusInterface {

  /**
   * Get the migration status object.
   *
   * @return \Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface
   *   The migration status object.
   */
  public function getMigrationStatusObject(): StatusInterface;

  /**
   * Set the migration status object.
   *
   * @param \Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface $status
   *   The migration status object.
   */
  public function setMigrationStatusObject(StatusInterface $status): void;

  /**
   * Delete the migration status object.
   */
  public function deleteMigrationStatusObject(): void;

  /**
   * Get the current migration status.
   *
   * @return string
   *   The current migration status.
   */
  public function getMigrationStatus(): string;

  /**
   * Update the migration status.
   *
   * @param string $status
   *   The new status.
   */
  public function updateMigrationStatus(string $status): void;

  /**
   * Delete the migration status.
   */
  public function deleteMigrationStatus(): void;

}
