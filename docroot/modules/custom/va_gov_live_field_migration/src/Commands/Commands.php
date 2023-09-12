<?php

namespace Drupal\va_gov_live_field_migration\Commands;

use Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface as FieldProviderResolverInterface;
use Drupal\va_gov_live_field_migration\Migration\Resolver\ResolverInterface as MigrationResolverInterface;
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
   * The migration resolver service.
   *
   * @var \Drupal\va_gov_live_field_migration\Migration\Resolver\ResolverInterface
   */
  protected $migrationResolver;

  /**
   * Commands constructor.
   *
   * @param \Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface $fieldProviderResolver
   *   The field provider resolver service.
   * @param \Drupal\va_gov_live_field_migration\Migration\Resolver\ResolverInterface $migrationResolver
   *   The migration resolver service.
   */
  public function __construct(
    FieldProviderResolverInterface $fieldProviderResolver,
    MigrationResolverInterface $migrationResolver
  ) {
    $this->migrationResolver = $migrationResolver;
    $this->fieldProviderResolver = $fieldProviderResolver;
  }

  /**
   * Perform an operation, such as migrating, rolling back, or verifying.
   *
   * @param callable $operation
   *   The operation to perform.
   */
  public function performOperation(callable $operation) {
    $startTime = microtime(TRUE);
    try {
      $operation();
    }
    catch (\Exception $exception) {
      $this->output()->writeln('Error: ' . $exception->getMessage());
    }
    finally {
      $elapsedTime = microtime(TRUE) - $startTime;
      $peakMemoryUsage = memory_get_peak_usage();
      $this->output()->writeln('Elapsed time: ' . number_format($elapsedTime, 2) . ' seconds');
      $this->output()->writeln('Peak memory usage: ' . number_format($peakMemoryUsage / 1024 / 1024, 2) . ' MB');
    }
  }

  /**
   * Migrate a specific field on a specific entity type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:migrate-field
   * @aliases va-gov-live-field-migration-migrate-field
   */
  public function migrateField(
    string $entityType,
    string $fieldName
  ) {
    $this->performOperation(function () use ($entityType, $fieldName) {
      $this->output()->writeln('Migrating field "' . $fieldName . '" on entity type "' . $entityType . '"...');
      $this->migrationResolver
        ->getMigration($entityType, $fieldName)
        ->runMigration($entityType, $fieldName);
      $this->output()->writeln('Migration successful.');
    });
  }

  /**
   * Rollback a specific field on a specific entity type.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @command va-gov-live-field-migration:rollback-field
   * @aliases va-gov-live-field-migration-rollback-field
   */
  public function rollbackField(
    string $entityType,
    string $fieldName
  ) {
    $this->performOperation(function () use ($entityType, $fieldName) {
      $this->output()->writeln('Rolling back migration of field "' . $fieldName . '" on entity type "' . $entityType . '"...');
      $this->migrationResolver
        ->getMigration($entityType, $fieldName)
        ->rollbackMigration($entityType, $fieldName);
      $this->output()->writeln('Rollback successful.');
    });
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
    $this->performOperation(function () use ($entityType, $fieldName) {
      $this->output()->writeln('Verifying migration of field "' . $fieldName . '" on entity type "' . $entityType . '"...');
      $this->migrationResolver
        ->getMigration($entityType, $fieldName)
        ->verifyMigration($entityType, $fieldName);
      $this->output()->writeln('Verification successful.');
    });
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
    $this->performOperation(function () use ($fieldProvider, $entityType, $bundle) {
      $this->output()->writeln('Finding fields on entity type "' . $entityType . '", bundle "' . ($bundle ?: 'NULL') . '"...');
      $fields = $this->fieldProviderResolver
        ->getFieldProvider($fieldProvider)
        ->getFields($entityType, $bundle);
      $this->output()->writeln('Found ' . count($fields) . ' fields.');
      foreach ($fields as $field) {
        $this->output()->writeln($field);
      }
    });
  }

}
