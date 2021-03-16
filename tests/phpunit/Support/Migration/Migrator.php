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
   * Run an import for the given migration ID.
   *
   * @param string $migration_id
   *   The migration machine name.
   * @param array $source_config_overrides
   *   An associative array of source configuration parameters to override.
   */
  public static function doImport(string $migration_id, array $source_config_overrides = []) : void {
    /** @var \Drupal\migrate\Plugin\MigrationPluginManager */
    $migrationManager = \Drupal::service('plugin.manager.migration');

    $migration = $migrationManager->createInstance($migration_id);

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
   * Clean up migration mappings for the given entity IDs.
   *
   * @param array $entity_ids
   *   Array of entity IDs for which to remove mappings.
   */
  public static function removeMigrationMappings(array $entity_ids) : void {
    // Get all migrate_map_% tables
    // Delete from tables where destid1 in node_ids.
  }

}
