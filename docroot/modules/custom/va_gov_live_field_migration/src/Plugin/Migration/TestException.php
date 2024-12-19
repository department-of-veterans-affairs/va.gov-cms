<?php

namespace Drupal\va_gov_live_field_migration\Plugin\Migration;

use Drupal\va_gov_live_field_migration\Exception\MigrationRollbackException;
use Drupal\va_gov_live_field_migration\Exception\MigrationRunException;
use Drupal\va_gov_live_field_migration\Exception\MigrationVerificationException;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginBase;

/**
 * Exception test migration.
 *
 * This always throws an exception.
 *
 * @Migration(
 *   id = "test_exception",
 *   label = @Translation("Exception Test")
 * )
 */
class TestException extends MigrationPluginBase {

  /**
   * {@inheritDoc}
   */
  public function runMigration(string $entityType, string $fieldName) : void {
    throw new MigrationRunException('This is a test exception.');
  }

  /**
   * {@inheritDoc}
   */
  public function rollbackMigration(string $entityType, string $fieldName) : void {
    throw new MigrationRollbackException('This is a test exception.');
  }

  /**
   * {@inheritDoc}
   */
  public function verifyMigration(string $entityType, string $fieldName) : void {
    throw new MigrationVerificationException('This is a test exception.');
  }

}
