<?php

namespace Drupal\va_gov_live_field_migration\Migration\Resolver;

use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface;

/**
 * An interface for the Resolver service.
 */
interface ResolverInterface {

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

}
