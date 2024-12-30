<?php

namespace Drupal\va_gov_live_field_migration\Commands;

use Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface as FieldProviderResolverInterface;
use Drupal\va_gov_live_field_migration\Migration\Runner\RunnerInterface;
use Drush\Attributes as CLI;
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
   */
  #[CLI\Command(name: 'va-gov-live-field-migration:migrate', aliases: ['va-gov-live-field-migration-migrate'])]
  #[CLI\Argument(name: 'entityType', description: 'The entity type')]
  #[CLI\Argument(name: 'fieldName', description: 'The field name')]
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
   */
  #[CLI\Command(name: 'va-gov-live-field-migration:rollback', aliases: ['va-gov-live-field-migration-rollback'])]
  #[CLI\Argument(name: 'entityType', description: 'The entity type')]
  #[CLI\Argument(name: 'fieldName', description: 'The field name')]
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
   */
  #[CLI\Command(name: 'va-gov-live-field-migration:verify', aliases: ['va-gov-live-field-migration-verify'])]
  #[CLI\Argument(name: 'entityType', description: 'The entity type')]
  #[CLI\Argument(name: 'fieldName', description: 'The field name')]
  public function verify(
    string $entityType,
    string $fieldName
  ) {
    $migration = $this->migrationRunner->getMigration($entityType, $fieldName);
    $this->migrationRunner->verifyMigration($migration, $entityType, $fieldName);
  }

  /**
   * Find fields that haven't been migrated yet.
   */
  #[CLI\Command(name: 'va-gov-live-field-migration:find', aliases: ['va-gov-live-field-migration-find'])]
  #[CLI\Option(name: 'field-provider', description: 'The field provider to use')]
  #[CLI\Option(name: 'entity-type', description: 'The entity type to use')]
  #[CLI\Option(name: 'bundle', description: 'The bundle to use')]
  public function find($options = [
    // Default to the issue 14995 field provider.
    // @see https://github.com/department-of-veterans-affairs/va-gov-cms/issues/14995
    'field-provider' => 'issue_14995',
    'entity-type' => 'node',
    'bundle' => NULL,
  ]) {
    $fieldProvider = $options['field-provider'];
    $entityType = $options['entity-type'];
    $bundle = $options['bundle'];
    $this->output()->writeln('Finding fields with field provider "' . $fieldProvider . '" on entity type "' . $entityType . '", bundle "' . ($bundle ?: 'NULL') . '"...');
    $fields = $this->fieldProviderResolver
      ->getFieldProvider($fieldProvider)
      ->getFields($entityType, $bundle);
    $this->output()->writeln('Found ' . count($fields) . ' fields.');
    foreach ($fields as $field) {
      $this->output()->writeln($field);
    }
  }

}
