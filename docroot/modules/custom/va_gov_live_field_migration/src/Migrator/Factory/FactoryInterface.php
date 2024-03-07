<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Factory;

use Drupal\va_gov_live_field_migration\Migrator\MigratorInterface;

/**
 * An interface for the migrator factory.
 */
interface FactoryInterface {

  /**
   * Get a migrator for `string` to `string_long` migrations.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return \Drupal\va_gov_live_field_migration\Migrator\MigratorInterface
   *   The migrator.
   */
  public function getStringToStringLongMigrator(string $entityType, string $fieldName): MigratorInterface;

  /**
   * Get a migrator for `text` to `string_long` migrations.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return \Drupal\va_gov_live_field_migration\Migrator\MigratorInterface
   *   The migrator.
   */
  public function getTextToStringLongMigrator(string $entityType, string $fieldName): MigratorInterface;

}
