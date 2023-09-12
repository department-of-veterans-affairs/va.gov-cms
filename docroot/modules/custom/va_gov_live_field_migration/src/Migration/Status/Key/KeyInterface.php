<?php

namespace Drupal\va_gov_live_field_migration\Migration\Status\Key;

/**
 * An interface for calculating migration status keys.
 */
interface KeyInterface {

  const KEY_PREFIX = 'va_gov_live_field_migration';

  /**
   * Gets the status key.
   *
   * This is a combination of the following:
   * - "va_gov_live_field_migration"
   * - The migration ID.
   * - The entity type.
   * - The field name.
   *
   * @return string
   *   The status key.
   */
  public static function getKey(string $migrationId, string $entityType, string $fieldName): string;

}
