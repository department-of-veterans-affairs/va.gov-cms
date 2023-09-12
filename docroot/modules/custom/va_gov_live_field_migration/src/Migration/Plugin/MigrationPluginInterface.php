<?php

namespace Drupal\va_gov_live_field_migration\Migration\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface for migration plugins.
 */
interface MigrationPluginInterface extends PluginInspectionInterface {

  /**
   * Runs a migration for the specified entity type and field name.
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
  public function runMigration(string $entityType, string $fieldName);

  /**
   * Rolls back a migration for the specified entity type and field name.
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
  public function rollbackMigration(string $entityType, string $fieldName);

  /**
   * Verifies a migration for the specified entity type and field name.
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
  public function verifyMigration(string $entityType, string $fieldName);

}
