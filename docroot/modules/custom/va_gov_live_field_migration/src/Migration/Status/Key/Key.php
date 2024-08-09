<?php

namespace Drupal\va_gov_live_field_migration\Migration\Status\Key;

/**
 * A class for calculating a status key.
 */
class Key implements KeyInterface {

  /**
   * {@inheritDoc}
   */
  public static function getKey(string $migrationId, string $entityType, string $fieldName): string {
    return implode('__', [
      KeyInterface::KEY_PREFIX,
      $migrationId,
      $entityType,
      $fieldName,
    ]);
  }

}
