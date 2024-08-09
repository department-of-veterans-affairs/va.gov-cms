<?php

namespace Drupal\va_gov_live_field_migration\Migrator;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_live_field_migration\Database\DatabaseInterface;
use Drupal\va_gov_live_field_migration\Exception\MigrationRollbackException;
use Drupal\va_gov_live_field_migration\Exception\MigrationVerificationException;
use Drupal\va_gov_live_field_migration\FieldPurger\FieldPurgerInterface;
use Drupal\va_gov_live_field_migration\Migrator\Traits\DatabaseMigrationInterface;
use Drupal\va_gov_live_field_migration\Migrator\Traits\DatabaseMigrationTrait;
use Drupal\va_gov_live_field_migration\Migrator\Traits\EntityDisplayOperationsInterface;
use Drupal\va_gov_live_field_migration\Migrator\Traits\EntityDisplayOperationsTrait;
use Drupal\va_gov_live_field_migration\Migrator\Traits\FieldStorageOperationsInterface;
use Drupal\va_gov_live_field_migration\Migrator\Traits\FieldStorageOperationsTrait;
use Drupal\va_gov_live_field_migration\Migrator\Traits\MigrationStatusInterface;
use Drupal\va_gov_live_field_migration\Migrator\Traits\MigrationStatusTrait;
use Drupal\va_gov_live_field_migration\Reporter\ReporterInterface;
use Drupal\va_gov_live_field_migration\State\StateInterface;

/**
 * Base class used for Migrator services.
 */
abstract class MigratorBase implements MigratorInterface, DatabaseMigrationInterface, EntityDisplayOperationsInterface, FieldStorageOperationsInterface, MigrationStatusInterface {

  use DatabaseMigrationTrait;
  use EntityDisplayOperationsTrait;
  use FieldStorageOperationsTrait;
  use MigrationStatusTrait;
  use StringTranslationTrait;

  /**
   * The migration state service.
   *
   * @var \Drupal\va_gov_live_field_migration\State\StateInterface
   */
  protected $state;

  /**
   * The database service.
   *
   * @var \Drupal\va_gov_live_field_migration\Database\DatabaseInterface
   */
  protected $database;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type repository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The field purger service.
   *
   * @var \Drupal\va_gov_live_field_migration\FieldPurger\FieldPurgerInterface
   */
  protected $fieldPurger;

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The field storage config backup.
   *
   * @var array
   */
  protected $fieldStorageConfigBackup = [];

  /**
   * The field storage config in progress.
   *
   * @var array
   */
  protected $workingFieldStorageConfig = [];

  /**
   * The field config backups.
   *
   * @var array[]
   */
  protected $fieldConfigBackups = [];

  /**
   * The working copy of the field configs.
   *
   * @var array[]
   */
  protected $workingFieldConfigs = [];

  /**
   * The form display config backups.
   *
   * @var array[]
   */
  protected $formDisplayConfigBackups = [];

  /**
   * The working copy of the form display configs.
   *
   * @var array[]
   */
  protected $workingFormDisplayConfigs = [];

  /**
   * The view display config backups.
   *
   * @var array[]
   */
  protected $viewDisplayConfigBackups = [];

  /**
   * The working copy of the view display configs.
   *
   * @var array[]
   */
  protected $workingViewDisplayConfigs = [];

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    ReporterInterface $reporter,
    StateInterface $state,
    DatabaseInterface $database,
    TranslationInterface $stringTranslation,
    EntityDisplayRepositoryInterface $entityDisplayRepository,
    EntityFieldManagerInterface $entityFieldManager,
    EntityTypeManagerInterface $entityTypeManager,
    EntityTypeRepositoryInterface $entityTypeRepository,
    FieldPurgerInterface $fieldPurger,
    string $entityType,
    string $fieldName
  ) {
    $this->reporter = $reporter;
    $this->state = $state;
    $this->database = $database;
    $this->stringTranslation = $stringTranslation;
    $this->entityDisplayRepository = $entityDisplayRepository;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeRepository = $entityTypeRepository;
    $this->fieldPurger = $fieldPurger;
    $this->entityType = $entityType;
    $this->fieldName = $fieldName;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityType(): string {
    return $this->entityType;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldName(): string {
    return $this->fieldName;
  }

  /**
   * {@inheritDoc}
   */
  protected function getState(): StateInterface {
    return $this->state;
  }

  /**
   * {@inheritDoc}
   */
  protected function getReporter(): ReporterInterface {
    return $this->reporter;
  }

  /**
   * {@inheritDoc}
   */
  protected function getDatabase(): DatabaseInterface {
    return $this->database;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityDisplayRepository(): EntityDisplayRepositoryInterface {
    return $this->entityDisplayRepository;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityFieldManager(): EntityFieldManagerInterface {
    return $this->entityFieldManager;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityTypeManager(): EntityTypeManagerInterface {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityTypeRepository(): EntityTypeRepositoryInterface {
    return $this->entityTypeRepository;
  }

  /**
   * Get the source type for this migration.
   *
   * @return string
   *   The source type.
   */
  protected function getSourceType(): string {
    return 'string';
  }

  /**
   * Get the destination type for this migration.
   *
   * @return string
   *   The destination type.
   */
  protected function getDestinationType(): string {
    return 'string_long';
  }

  /**
   * Runs a migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationRunException
   *   If the migration run fails.
   */
  public function run() {
    $this->verifyField($this->getSourceType());
    $this->backupFieldTables();
    $this->fieldConfigs = [];
    $this->formDisplayConfigs = [];
    $this->viewDisplayConfigs = [];
    $this->backupFieldStorageConfig();
    $bundles = $this->getFieldBundles();
    foreach ($bundles as $bundle => $label) {
      $this->reporter->reportInfo("Backing up data for field {$this->fieldName} on bundle {$bundle} ({$label})...");
      $this->backupFieldConfig($bundle);
      $this->backupFormDisplayConfig($bundle);
      $this->backupViewDisplayConfig($bundle);
    }
    $this->deleteFieldStorageConfig();
    $this->fieldPurger->purge();
    $this->alterFieldStorageConfig($this->getDestinationType());
    $this->createFieldStorageConfig($this->workingFieldStorageConfig);
    $this->alterFieldConfigs($this->getDestinationType());
    $this->createFieldConfigs($this->workingFieldConfigs);
    foreach ($bundles as $bundle => $label) {
      $this->alterFormDisplayConfig($bundle);
      $this->alterViewDisplayConfig($bundle);
      $this->updateFormDisplayConfig($bundle);
      $this->updateViewDisplayConfig($bundle);
    }
    // $this->database
    // ->restorePrimaryFieldTable($this->entityType, $this->fieldName, TRUE);
    // $this->database
    // ->restoreFieldRevisionTable($this->entityType, $this->fieldName, TRUE);
  }

  /**
   * Backup the field storage config.
   */
  public function backupFieldStorageConfig(): void {
    $this->reporter->reportInfo("Backing up field storage config for field {$this->fieldName}...");
    $fieldStorageConfig = $this->getFieldStorageConfig();
    $fieldStorageConfigArray = $fieldStorageConfig->toArray();
    $this->fieldStorageConfigBackup = $fieldStorageConfigArray;
    $this->workingFieldStorageConfig = $fieldStorageConfigArray;
  }

  /**
   * Alter the field storage config.
   *
   * @param string $destinationType
   *   The destination type.
   * @param string $destinationSettings
   *   The destination settings.
   */
  public function alterFieldStorageConfig(string $destinationType, array $destinationSettings = []): void {
    $this->reporter->reportInfo("Altering field storage config for field {$this->fieldName}...");
    $this->workingFieldStorageConfig['type'] = $destinationType;
    $this->workingFieldStorageConfig['settings'] = $destinationSettings;
  }

  /**
   * Restore the field storage config.
   *
   * @param array $fieldStorageConfig
   *   The field storage config.
   */
  public function restoreFieldStorageConfig(array $fieldStorageConfig): void {
    $this->reporter->reportInfo("Restoring field storage config for field {$this->fieldName}...");
    $this->workingFieldStorageConfig = $fieldStorageConfig;
  }

  /**
   * Backup the field config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function backupFieldConfig(string $bundle): void {
    $this->reporter->reportInfo("Backing up field config for field {$this->fieldName} on bundle {$bundle}...");
    $fieldConfig = $this->getFieldConfig($bundle);
    $fieldConfigArray = $fieldConfig->toArray();
    $this->fieldConfigBackups[$bundle] = $fieldConfigArray;
    $this->workingFieldConfigs[$bundle] = $fieldConfigArray;
  }

  /**
   * Alter the field configs.
   *
   * @param string $destinationType
   *   The destination type.
   * @param string $destinationSettings
   *   The destination settings.
   */
  public function alterFieldConfigs(string $destinationType, array $destinationSettings = []): void {
    $this->reporter->reportInfo("Altering field configs for field {$this->fieldName}...");
    foreach ($this->workingFieldConfigs as $bundle => $fieldConfig) {
      $this->alterFieldConfig($bundle, $destinationType, $destinationSettings);
    }
  }

  /**
   * Alter the field config.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $destinationType
   *   The destination type.
   * @param string $destinationSettings
   *   The destination settings.
   */
  public function alterFieldConfig(string $bundle, string $destinationType, array $destinationSettings = []): void {
    $this->reporter->reportInfo("Altering field config for field {$this->fieldName} on bundle {$bundle}...");
    $this->workingFieldConfigs[$bundle]['type'] = $destinationType;
    $this->workingFieldConfigs[$bundle]['settings'] = $destinationSettings;
  }

  /**
   * Backup the form display config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function backupFormDisplayConfig(string $bundle): void {
    $this->reporter->reportInfo("Backing up form display config for field {$this->fieldName} on bundle {$bundle}...");
    $formModeOptions = $this->getFormModeOptions($bundle);
    foreach ($formModeOptions as $formMode => $options) {
      $formDisplayConfig = $this->getFormDisplayConfig($bundle, $formMode);
      $formDisplayConfigArray = $formDisplayConfig->toArray();
      $this->formDisplayConfigBackups[$bundle][$formMode] = $formDisplayConfigArray;
      $this->workingFormDisplayConfigs[$bundle][$formMode] = $formDisplayConfigArray;
    }
  }

  /**
   * Alter the form display config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function alterFormDisplayConfig(string $bundle): void {
    $this->reporter->reportInfo("Altering form display config for field {$this->fieldName} on bundle {$bundle}...");
    $formModeOptions = $this->getFormModeOptions($bundle);
    foreach ($formModeOptions as $formMode => $options) {
      $this->alterFormDisplayConfigForMode($bundle, $formMode);
    }
  }

  /**
   * Alter the form display config for a specific mode.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $formMode
   *   The form mode.
   */
  public function alterFormDisplayConfigForMode(string $bundle, string $formMode): void {
    $this->reporter->reportInfo("Altering form display config for field {$this->fieldName} on bundle {$bundle} for form mode {$formMode}...");
    $savedConfig = $this->workingFormDisplayConfigs[$bundle][$formMode]['content'];
    $fieldName = $this->fieldName;
    // If not present, then it is disabled and we don't need to care about it.
    if (!isset($savedConfig[$fieldName])) {
      return;
    }
    $desiredType = $this->getDestinationType() . '_textfield_with_counter';
    // This already has the desired configuration.
    if ($savedConfig[$fieldName]['type'] === $desiredType) {
      return;
    }
    $savedConfig[$fieldName]['type'] = $desiredType;
    $savedConfig[$fieldName]['settings']['maxlength'] = $this->workingFieldStorageConfig['settings']['max_length'];
    $this->workingFormDisplayConfigs[$bundle][$formMode]['content'] = $savedConfig;
  }

  /**
   * Update the form display config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function updateFormDisplayConfig(string $bundle): void {
    $this->reporter->reportInfo("Updating form display config for field {$this->fieldName} on bundle {$bundle}...");
    $formModeOptions = $this->getFormModeOptions($bundle);
    foreach ($formModeOptions as $formMode => $options) {
      $this->updateFormDisplayConfigForMode($bundle, $formMode, $this->workingFormDisplayConfigs[$bundle][$formMode]['content']);
    }
  }

  /**
   * Update the form display config for a specific mode.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $formMode
   *   The form mode.
   * @param array $config
   *   The config.
   */
  public function updateFormDisplayConfigForMode(string $bundle, string $formMode, array $config): void {
    $this->reporter->reportInfo("Updating form display config for field {$this->fieldName} on bundle {$bundle} for form mode {$formMode}...");
    $this->getEntityDisplayRepository()
      ->getFormDisplay($this->entityType, $bundle, $formMode)
      ->setComponent($this->fieldName, $config)
      ->save();
  }

  /**
   * Backup the view display config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function backupViewDisplayConfig(string $bundle): void {
    $this->reporter->reportInfo("Backing up view display config for field {$this->fieldName} on bundle {$bundle}...");
    $viewModeOptions = $this->getViewModeOptions($bundle);
    foreach ($viewModeOptions as $viewMode => $options) {
      $viewDisplayConfig = $this->getViewDisplayConfig($bundle, $viewMode);
      $viewDisplayConfigArray = $viewDisplayConfig->toArray();
      $this->viewDisplayConfigBackups[$bundle][$viewMode] = $viewDisplayConfigArray;
      $this->workingViewDisplayConfigs[$bundle][$viewMode] = $viewDisplayConfigArray;
    }
  }

  /**
   * Alter the view display config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function alterViewDisplayConfig(string $bundle): void {
    $this->reporter->reportInfo("Altering view display config for field {$this->fieldName} on bundle {$bundle}...");
    $viewModeOptions = $this->getViewModeOptions($bundle);
    foreach ($viewModeOptions as $viewMode => $options) {
      $this->alterViewDisplayConfigForMode($bundle, $viewMode);
    }
  }

  /**
   * Alter the view display config for a specific mode.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $viewMode
   *   The view mode.
   */
  public function alterViewDisplayConfigForMode(string $bundle, string $viewMode): void {
    $this->reporter->reportInfo("Altering view display config for field {$this->fieldName} on bundle {$bundle} for view mode {$viewMode}...");
    $savedConfig = $this->workingViewDisplayConfigs[$bundle][$viewMode];
    print_r($savedConfig);
    $fieldName = $this->fieldName;
    // If not present, then it is disabled and we don't need to care about it.
    if (!isset($savedConfig[$fieldName])) {
      return;
    }
    // We're just replacing the old settings, so this is basically a no-op.
    $this->workingViewDisplayConfigs[$bundle][$viewMode] = $savedConfig;
  }

  /**
   * Update the view display config.
   *
   * @param string $bundle
   *   The bundle.
   */
  public function updateViewDisplayConfig(string $bundle): void {
    $this->reporter->reportInfo("Updating view display config for field {$this->fieldName} on bundle {$bundle}...");
    $viewModeOptions = $this->getViewModeOptions($bundle);
    foreach ($viewModeOptions as $viewMode => $options) {
      $this->updateViewDisplayConfigForMode($bundle, $viewMode, $this->workingViewDisplayConfigs[$bundle][$viewMode]['content']);
    }
  }

  /**
   * Update the view display config for a specific mode.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $viewMode
   *   The view mode.
   * @param array $config
   *   The config.
   */
  public function updateViewDisplayConfigForMode(string $bundle, string $viewMode, array $config): void {
    $this->reporter->reportInfo("Updating view display config for field {$this->fieldName} on bundle {$bundle} for view mode {$viewMode}...");
    $this->getEntityDisplayRepository()
      ->getViewDisplay($this->entityType, $bundle, $viewMode)
      ->setComponent($this->fieldName, $config)
      ->save();
  }

  /**
   * Rolls back a migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationRollbackException
   *   If the migration rollback fails.
   */
  public function rollback() {
    throw new MigrationRollbackException('Not implemented.');
  }

  /**
   * Verifies the migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationVerificationException
   *   If the migration verification fails.
   */
  public function verify() {
    throw new MigrationVerificationException('Not implemented.');
  }

}
