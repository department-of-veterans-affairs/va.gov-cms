<?php

namespace Drupal\va_gov_live_field_migration\Plugin\Migration;

use Drupal\va_gov_live_field_migration\Exception\MigrationErrorException;
use Drupal\va_gov_live_field_migration\Exception\MigrationRollbackException;
use Drupal\va_gov_live_field_migration\Exception\MigrationVerificationException;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginBase;

/**
 * Migrate a text field to a string_long field.
 *
 * @Migration(
 *   id = "text_to_string_long",
 *   label = @Translation("text to string_long")
 * )
 */
class TextToStringLong extends MigrationPluginBase {

  /**
   * {@inheritDoc}
   */
  public function runMigration(string $entityType, string $fieldName) : void {
    throw new MigrationErrorException('Not implemented.');
  }

  /**
   * {@inheritDoc}
   */
  public function rollbackMigration(string $entityType, string $fieldName) : void {
    throw new MigrationRollbackException('Not implemented.');
  }

  /**
   * {@inheritDoc}
   */
  public function verifyMigration(string $entityType, string $fieldName) : void {
    throw new MigrationVerificationException('Not implemented.');
  }

}
