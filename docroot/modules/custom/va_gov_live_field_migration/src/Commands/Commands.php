<?php

namespace Drupal\va_gov_live_field_migration\Commands;

use Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface as FieldProviderResolverInterface;
use Drupal\va_gov_live_field_migration\Migration\Runner\RunnerInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for live field migrations.
 */
class Commands extends DrushCommands {

  /**
   * The field provider resolver service.
   *
   * @var \Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface
   */
  protected $fieldProviderResolver;

  /**
   * The migration runner service.
   *
   * @var \Drupal\va_gov_live_field_migration\Migration\Runner\RunnerInterface
   */
  protected $migrationRunner;

  /**
   * Commands constructor.
   *
   * @param \Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface $fieldProviderResolver
   *   The field provider resolver service.
   * @param \Drupal\va_gov_live_field_migration\Migration\Runner\RunnerInterface $migrationRunner
   *   The migration runner service.
   */
  public function __construct(
    FieldProviderResolverInterface $fieldProviderResolver,
    RunnerInterface $migrationRunner
  ) {
    $this->fieldProviderResolver = $fieldProviderResolver;
    $this->migrationRunner = $migrationRunner;
  }

  /**
   * Migrate a specific field on a specific entity type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:migrate
   * @aliases va-gov-live-field-migration-migrate
   */
  public function migrate(
    string $entityType,
    string $fieldName
  ) {
    $migration = $this->migrationRunner->getMigration($entityType, $fieldName);
    $this->migrationRunner->runMigration($migration, $entityType, $fieldName);
  }

  /**
   * Rollback a specific field on a specific entity type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:rollback
   * @aliases va-gov-live-field-migration-rollback
   */
  public function rollback(
    string $entityType,
    string $fieldName
  ) {
    $migration = $this->migrationRunner->getMigration($entityType, $fieldName);
    $this->migrationRunner->rollbackMigration($migration, $entityType, $fieldName);
  }

  /**
   * Verify a migration completed successfully.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:verify
   * @aliases va-gov-live-field-migration-verify
   */
  public function verify(
    string $entityType,
    string $fieldName
  ) {
    $migration = $this->migrationRunner->getMigration($entityType, $fieldName);
    $this->migrationRunner->verifyMigration($migration, $entityType, $fieldName);
  }

  /**
   * Find fields that haven't been migrated yet.
   *
   * @param string|null $fieldProvider
   *   The field provider.
   * @param string|null $entityType
   *   The entity type.
   * @param string|null $bundle
   *   The entity bundle or content type.
   *
   * @command va-gov-live-field-migration:find
   * @aliases va-gov-live-field-migration-find
   */
  public function find(
    string $fieldProvider = NULL,
    string $entityType = NULL,
    string $bundle = NULL
  ) {
    if ($fieldProvider === NULL) {
      $fieldProvider = 'test_empty_list';
    }
    if ($entityType === NULL) {
      $entityType = 'node';
    }
    $this->output()->writeln('Finding fields on entity type "' . $entityType . '", bundle "' . ($bundle ?: 'NULL') . '"...');
    $fields = $this->fieldProviderResolver
      ->getFieldProvider($fieldProvider)
      ->getFields($entityType, $bundle);
    $this->output()->writeln('Found ' . count($fields) . ' fields.');
    foreach ($fields as $field) {
      $this->output()->writeln($field);
    }
  }

}
