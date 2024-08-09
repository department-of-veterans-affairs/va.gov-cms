<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

/**
 * Separate out database migration-specific methods.
 */
interface DatabaseMigrationInterface {

  /**
   * Drop a table.
   *
   * @param string $table
   *   The table to drop.
   */
  public function dropTable(string $table): void;

  /**
   * Back up a field's tables.
   *
   * This backs up:
   * - primary data table.
   * - revision table.
   */
  public function backupFieldTables(): void;

}
