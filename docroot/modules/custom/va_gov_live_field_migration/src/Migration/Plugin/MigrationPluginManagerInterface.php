<?php

namespace Drupal\va_gov_live_field_migration\Migration\Plugin;

/**
 * An interface for the migration plugin manager.
 */
interface MigrationPluginManagerInterface {

  /**
   * Get the migration plugin.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @return \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface
   *   The strategy plugin.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException
   *   If the strategy cannot be found.
   */
  public function getMigration(string $id) : MigrationPluginInterface;

}
