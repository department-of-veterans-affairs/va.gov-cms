<?php

namespace Drupal\va_gov_live_field_migration\Database;

use Drupal\Core\Database\Connection;

/**
 * A database service to abstract database operations.
 */
class Database implements DatabaseInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    Connection $connection
  ) {
    $this->connection = $connection;
  }

  /**
   * {@inheritDoc}
   */
  public function dropTable(string $table): void {
    $this->connection->schema()->dropTable($table);
  }

  /**
   * {@inheritDoc}
   */
  public function getPrimaryFieldTableName(string $entityType, string $fieldName): string {
    return "{$entityType}__{$fieldName}";
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldRevisionTableName(string $entityType, string $fieldName): string {
    return "{$entityType}__{$fieldName}";
  }

  /**
   * {@inheritDoc}
   */
  public function getBackupTableName(string $tableName): string {
    // Table names are limited to 64 characters, but Drupal field tables can
    // be longer than that. So we need to truncate the table name.
    $tableName = substr($tableName, 0, 64 - strlen(self::BACKUP_TABLE_SUFFIX));
    return "{$tableName}__backup";
  }

  /**
   * {@inheritDoc}
   */
  public function createTable(string $newTable, string $existingTable): void {
    $this->connection->query("CREATE TABLE {$newTable} LIKE {$existingTable};");
  }

  /**
   * {@inheritDoc}
   */
  public function copyTable(string $sourceTable, string $destinationTable, bool $preserve = FALSE): void {
    if (!$preserve) {
      $this->dropTable($destinationTable);
      $this->createTable($destinationTable, $sourceTable);
    }
    $this->connection->query("INSERT {$destinationTable} SELECT * FROM {$sourceTable};");
  }

  /**
   * {@inheritDoc}
   */
  public function backupPrimaryFieldTable(string $entityType, string $fieldName, bool $preserve = FALSE): void {
    $primaryTable = $this->getPrimaryFieldTableName($entityType, $fieldName);
    $backupTable = $this->getBackupTableName($primaryTable);
    $this->copyTable($primaryTable, $backupTable, $preserve);
  }

  /**
   * {@inheritDoc}
   */
  public function backupFieldRevisionTable(string $entityType, string $fieldName, bool $preserve = FALSE): void {
    $revisionTable = $this->getFieldRevisionTableName($entityType, $fieldName);
    $backupTable = $this->getBackupTableName($revisionTable);
    $this->copyTable($revisionTable, $backupTable, $preserve);
  }

  /**
   * {@inheritDoc}
   */
  public function restorePrimaryFieldTable(string $entityType, string $fieldName, bool $preserve = FALSE): void {
    $primaryTable = $this->getPrimaryFieldTableName($entityType, $fieldName);
    $backupTable = $this->getBackupTableName($primaryTable);
    $this->copyTable($backupTable, $primaryTable, $preserve);
  }

  /**
   * {@inheritDoc}
   */
  public function restoreFieldRevisionTable(string $entityType, string $fieldName, bool $preserve = FALSE): void {
    $revisionTable = $this->getFieldRevisionTableName($entityType, $fieldName);
    $backupTable = $this->getBackupTableName($revisionTable);
    $this->copyTable($backupTable, $revisionTable, $preserve);
  }

}
