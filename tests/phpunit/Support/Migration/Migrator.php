<?php

namespace Tests\Support\Migration;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * A helper class to run migrations.
 */
class Migrator {

  /**
   * Get a Drupal migration.
   *
   * @param string $migration_id
   *   The ID of the migration.
   *
   * @return \Drupal\migrate\Plugin\Migration
   *   The migration.
   */
  private static function getMigration($migration_id) {
    /** @var \Drupal\migrate\Plugin\MigrationPluginManager */
    $migrationManager = \Drupal::service('plugin.manager.migration');

    return $migrationManager->createInstance($migration_id);
  }

  /**
   * Run an import for the given migration ID.
   *
   * @param string $migration_id
   *   The migration machine name.
   * @param array $source_config_overrides
   *   An associative array of source configuration parameters to override.
   */
  public static function doImport(string $migration_id, array $source_config_overrides = []) : void {
    /** @var \Drupal\migrate\Plugin\Migration */
    $migration = self::getMigration($migration_id);

    foreach ($source_config_overrides as $key => $value) {
      $source_config = $migration->getSourceConfiguration();
      $source_config[$key] = $value;
      $migration->set('source', $source_config);
    }

    $status = $migration->getStatus();
    if ($status !== MigrationInterface::STATUS_IDLE) {
      $migration->setStatus(MigrationInterface::STATUS_IDLE);
    }
    $migration->getIdMap()->prepareUpdate();
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Delete migration mapping for the given migration & source ID.
   *
   * @param string $migration_id
   *   The migration ID.
   * @param string $source_id
   *   The source ID for which to remove the mapping.
   */
  public static function removeMigrationMapping(string $migration_id, string $source_id) : void {
    self::getMigration($migration_id)->getIdMap()->delete([$source_id]);
  }

}
