<?php

namespace Drupal\va_gov_live_field_migration\Migration\Runner;

use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface;
use Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface as FieldProviderResolverInterface;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface;
use Drupal\va_gov_live_field_migration\Migration\Resolver\ResolverInterface as MigrationResolverInterface;
use Drupal\va_gov_live_field_migration\Reporter\ReporterInterface;
use Drupal\va_gov_live_field_migration\State\StateInterface;

/**
 * The migration runner service.
 */
class Runner implements RunnerInterface {

  /**
   * The field provider resolver.
   *
   * @var \Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface
   */
  protected $fieldProviderResolver;

  /**
   * The migration resolver.
   *
   * @var \Drupal\va_gov_live_field_migration\Migration\Resolver\ResolverInterface
   */
  protected $migrationResolver;

  /**
   * The reporter.
   *
   * @var \Drupal\va_gov_live_field_migration\Reporter\ReporterInterface
   */
  protected $reporter;

  /**
   * The state.
   *
   * @var \Drupal\va_gov_live_field_migration\State\StateInterface
   */
  protected $state;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface $fieldProviderResolver
   *   The field provider resolver.
   * @param \Drupal\va_gov_live_field_migration\Migration\Resolver\ResolverInterface $migrationResolver
   *   The migration resolver.
   * @param \Drupal\va_gov_live_field_migration\Reporter\ReporterInterface $reporter
   *   The reporter.
   * @param \Drupal\va_gov_live_field_migration\State\StateInterface $state
   *   The state.
   */
  public function __construct(
    FieldProviderResolverInterface $fieldProviderResolver,
    MigrationResolverInterface $migrationResolver,
    ReporterInterface $reporter,
    StateInterface $state
  ) {
    $this->fieldProviderResolver = $fieldProviderResolver;
    $this->migrationResolver = $migrationResolver;
    $this->reporter = $reporter;
    $this->state = $state;
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
      $this->reporter->reportError('Error: ' . $exception->getMessage());
    }
    finally {
      $elapsedTime = microtime(TRUE) - $startTime;
      $peakMemoryUsage = memory_get_peak_usage();
      $this->reporter->reportInfo('Elapsed time: ' . number_format($elapsedTime, 2) . ' seconds');
      $this->reporter->reportInfo('Peak memory usage: ' . number_format($peakMemoryUsage / 1024 / 1024, 2) . ' MB');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldProvider(string $id): FieldProviderPluginInterface {
    return $this->fieldProviderResolver->getFieldProvider($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getMigration(string $entityType, string $fieldName) : MigrationPluginInterface {
    return $this->migrationResolver->getMigration($entityType, $fieldName);
  }

  /**
   * {@inheritdoc}
   */
  public function runMigration(MigrationPluginInterface $migration, string $entityType, string $fieldName) : void {
    $this->performOperation(function () use ($migration, $entityType, $fieldName) {
      $this->reporter->reportInfo('Migrating field "' . $fieldName . '" on entity type "' . $entityType . '"...');
      $migration->runMigration($entityType, $fieldName);
      $this->reporter->reportInfo('Migration successful.');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function rollbackMigration(MigrationPluginInterface $migration, string $entityType, string $fieldName) : void {
    $this->performOperation(function () use ($migration, $entityType, $fieldName) {
      $this->reporter->reportInfo('Rolling back field "' . $fieldName . '" on entity type "' . $entityType . '"...');
      $migration->rollbackMigration($entityType, $fieldName);
      $this->reporter->reportInfo('Rollback successful.');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function verifyMigration(MigrationPluginInterface $migration, string $entityType, string $fieldName) : void {
    $this->performOperation(function () use ($migration, $entityType, $fieldName) {
      $this->reporter->reportInfo('Verifying field "' . $fieldName . '" on entity type "' . $entityType . '"...');
      $migration->rollbackMigration($entityType, $fieldName);
      $this->reporter->reportInfo('Verification successful.');
    });
  }

}
