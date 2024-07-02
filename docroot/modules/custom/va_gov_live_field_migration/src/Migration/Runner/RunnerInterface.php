<?php

namespace Drupal\va_gov_live_field_migration\Migration\Runner;

use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface;

/**
 * An interface for the migration runner service.
 */
interface RunnerInterface {

  /**
   * Get the field provider plugin.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @return \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface
   *   The field provider plugin.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\FieldProviderNotFoundException
   *   If the field provider cannot be found.
   */
  public function getFieldProvider(string $id): FieldProviderPluginInterface;

  /**
   * Get the migration plugin.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface
   *   The strategy plugin.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException
   *   If the strategy cannot be found.
   */
  public function getMigration(string $entityType, string $fieldName) : MigrationPluginInterface;

  /**
   * Run the migrations.
   *
   * @param \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface $migration
   *   The strategy plugin.
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException
   *   If the strategy cannot be found.
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationErrorException
   *   If the migration fails.
   */
  public function runMigration(MigrationPluginInterface $migration, string $entityType, string $fieldName) : void;

  /**
   * Rolls back a migration for the specified entity type and field name.
   *
   * @param \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface $migration
   *   The strategy plugin.
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException
   *   If the strategy cannot be found.
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationRollbackException
   *   If the rollback fails.
   */
  public function rollbackMigration(MigrationPluginInterface $migration, string $entityType, string $fieldName);

  /**
   * Verifies a migration for the specified entity type and field name.
   *
   * @param \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface $migration
   *   The strategy plugin.
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException
   *   If the strategy cannot be found.
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationVerificationException
   *   If the verification fails.
   */
  public function verifyMigration(MigrationPluginInterface $migration, string $entityType, string $fieldName);

}
