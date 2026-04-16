<?php

namespace Drupal\va_gov_eca\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate_tools\MigrateTools;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Execute a migration import.
 */
#[Action(
  id: 'execute_migration_import',
  label: new TranslatableMarkup("Execute migration import.")
)]
class ExecuteMigrationImport extends ConfigurableActionBase {

  /**
   * The migration plugin manager service.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected MigrationPluginManagerInterface $migrationPluginManager;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->migrationPluginManager = $container->get('plugin.manager.migration');
    $instance->logger = $container->get('logger.channel.eca');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'migration' => '',
      'update' => FALSE,
      'force' => FALSE,
      'limit' => '',
      'idlist' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritDoc}
   */
  public function execute() {
    try {
      // Get the migration instance.
      $migration = $this->migrationPluginManager->createInstance($this->configuration['migration']);

      // Check and set migration status.
      $status = $migration->getStatus();
      if ($status !== MigrationInterface::STATUS_IDLE) {
        $migration->setStatus(MigrationInterface::STATUS_IDLE);
      }
      // Prepare the migration for update if needed.
      $migration->getIdMap()->prepareUpdate();

      // Create message handler and executable.
      $message = new MigrateMessage();
      $executable = new MigrateExecutable($migration, $message);

      // Get the options for the migration.
      $this->buildOptions($migration);

      // Execute the migration.
      $executable->import();
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to execute migration @migration: @error', [
        '@migration' => $this->configuration['migration'],
        '@error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Builds options for a migration plugin.
   *
   * @param \Drupal\migrate\Plugin\Migration $migration
   *   The migration plugin to build options for.
   */
  protected function buildOptions(Migration $migration): void {
    $options = [
      'limit' => $this->configuration['limit'],
      'update' => $this->configuration['update'],
      'force' => $this->configuration['force'],
    ];

    if ($idlist = $this->configuration['idlist']) {
      $options['idlist'] = $idlist;
    }

    if ($options['limit']) {
      $migration->set('limit', $options['limit']);
    }
    if ($options['update']) {
      if (!$options['idlist']) {
        $migration->getIdMap()->prepareUpdate();
      }
      else {
        $source_id_values_list = MigrateTools::buildIdList($options);
        $keys = array_keys($migration->getSourcePlugin()->getIds());
        foreach ($source_id_values_list as $source_id_values) {
          $migration->getIdMap()->setUpdate(array_combine($keys, $source_id_values));
        }
      }
    }
    if ($options['force']) {
      $migration->set('requirements', []);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Get all available migrations.
    $migrations = $this->migrationPluginManager->createInstances([]);
    $options = ['' => $this->t('- Select -')];
    foreach ($migrations as $migration) {
      $options[$migration->id()] = $migration->label();
    }

    $form['migration'] = [
      '#type' => 'select',
      '#title' => $this->t('Migration'),
      '#options' => $options,
      '#default_value' => $this->configuration['migration'],
      '#required' => TRUE,
    ];

    $form['update'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Update existing content'),
      '#default_value' => $this->configuration['update'],
      '#description' => $this->t('If checked, existing content will be updated.'),
    ];

    $form['force'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force'),
      '#default_value' => $this->configuration['force'],
      '#description' => $this->t('Force an operation to run, even if all requirements are not met.'),
    ];

    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#default_value' => $this->configuration['limit'],
      '#description' => $this->t('Limit the number of items to process. Leave empty for no limit.'),
      '#min' => 1,
    ];

    $form['idlist'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ID List'),
      '#default_value' => $this->configuration['idlist'],
      '#description' => $this->t('Comma-separated list of IDs to process.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['migration'] = $form_state->getValue('migration');
    $this->configuration['update'] = $form_state->getValue('update');
    $this->configuration['force'] = $form_state->getValue('force');
    $this->configuration['limit'] = $form_state->getValue('limit');
    $this->configuration['idlist'] = $form_state->getValue('idlist');
  }

}
