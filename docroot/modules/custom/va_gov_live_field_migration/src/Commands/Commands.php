<?php

namespace Drupal\va_gov_live_field_migration\Commands;

use Drush\Commands\DrushCommands;

/**
 * Drush commands for live field migrations.
 */
class Commands extends DrushCommands {

  /**
   * Migrate a specific field on a specific content type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $bundle
   *   The entity bundle or content type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:migrate-field
   * @aliases va-gov-live-field-migration-migrate-field
   */
  public function migrateField(
    string $entityType,
    string $bundle,
    string $fieldName
  ) {
    $this->output()->writeln('Migrating field ' . $fieldName . ' on ' . $entityType . ' ' . $bundle);
  }

  /**
   * Rollback a specific field on a specific content type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $bundle
   *   The entity bundle or content type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:rollback-field
   * @aliases va-gov-live-field-migration-rollback-field
   */
  public function rollbackField(
    string $entityType,
    string $bundle,
    string $fieldName
  ) {
    $this->output()->writeln('Rolling back field ' . $fieldName . ' on ' . $entityType . ' ' . $bundle);
  }

  /**
   * Verify a migration completed successfully.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $bundle
   *   The entity bundle or content type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:verify
   * @aliases va-gov-live-field-migration-verify
   */
  public function verify(
    string $entityType,
    string $bundle,
    string $fieldName
  ) {
    $this->output()->writeln('Verifying field ' . $fieldName . ' on ' . $entityType . ' ' . $bundle);
  }

  /**
   * Find fields that haven't been migrated yet.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $bundle
   *   The entity bundle or content type.
   *
   * @command va-gov-live-field-migration:find
   * @aliases va-gov-live-field-migration-find
   */
  public function find(
    string $entityType,
    string $bundle
  ) {
    $this->output()->writeln('Finding fields on ' . $entityType . ' ' . $bundle);
  }

}
