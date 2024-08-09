<?php

namespace Drupal\va_gov_live_field_migration\Database;

/**
 * An interface for the Database service.
 */
interface DatabaseInterface {

  const BACKUP_TABLE_SUFFIX = '__backup';

  /**
   * Drop a table.
   *
   * @param string $table
   *   The table name.
   */
  public function dropTable(string $table): void;

  /**
   * Calculate the name of the primary field table.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return string
   *   The name of the table.
   */
  public function getPrimaryFieldTableName(string $entityType, string $fieldName): string;

  /**
   * Calculate the name of the field revision table.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return string
   *   The name of the table.
   */
  public function getFieldRevisionTableName(string $entityType, string $fieldName): string;

  /**
   * Calculate the name of the backup table.
   *
   * @param string $tableName
   *   The name of the table.
   *
   * @return string
   *   The name of the backup table.
   */
  public function getBackupTableName(string $tableName): string;

  /**
   * Create a new table, using an existing table as a foundation.
   *
   * @param string $newTable
   *   The name of the new table.
   * @param string $existingTable
   *   The name of the existing table.
   */
  public function createTable(string $newTable, string $existingTable): void;

  /**
   * Copy a table to a new table.
   *
   * @param string $sourceTable
   *   The source table.
   * @param string $destinationTable
   *   The destination table.
   * @param bool $preserve
   *   Whether to preserve the destination table if it exists.
   */
  public function copyTable(string $sourceTable, string $destinationTable, bool $preserve = FALSE): void;

  /**
   * Back up a field's primary table.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   * @param bool $preserve
   *   Whether to preserve the destination table if it exists.
   */
  public function backupPrimaryFieldTable(string $entityType, string $fieldName, bool $preserve = FALSE): void;

  /**
   * Back up a field's revision table.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   * @param bool $preserve
   *   Whether to preserve the destination table if it exists.
   */
  public function backupFieldRevisionTable(string $entityType, string $fieldName, bool $preserve = FALSE): void;

  /**
   * Restore a field's primary table from backup.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   * @param bool $preserve
   *   Whether to preserve the destination table if it exists.
   */
  public function restorePrimaryFieldTable(string $entityType, string $fieldName, bool $preserve = FALSE): void;

  /**
   * Restore a field's revision table from backup.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   * @param bool $preserve
   *   Whether to preserve the destination table if it exists.
   */
  public function restoreFieldRevisionTable(string $entityType, string $fieldName, bool $preserve = FALSE): void;

}
