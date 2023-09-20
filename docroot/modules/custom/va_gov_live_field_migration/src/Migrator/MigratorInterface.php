<?php

namespace Drupal\va_gov_live_field_migration\Migrator;

/**
 * An interface for migrator services.
 */
interface MigratorInterface {

  /**
   * Runs a migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationRunException
   *   If the migration run fails.
   */
  public function run();

  /**
   * Rolls back a migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationRollbackException
   *   If the migration rollback fails.
   */
  public function rollback();

  /**
   * Verifies the migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationVerificationException
   *   If the migration verification fails.
   */
  public function verify();

}
