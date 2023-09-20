<?php

namespace Drupal\va_gov_live_field_migration\Migration\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_live_field_migration\Migrator\Factory\FactoryInterface;
use Drupal\va_gov_live_field_migration\Reporter\ReporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class used for Migration plugins.
 */
abstract class MigrationPluginBase extends PluginBase implements MigrationPluginInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The reporter service.
   *
   * @var \Drupal\va_gov_live_field_migration\Reporter\ReporterInterface
   */
  protected $reporter;

  /**
   * The migration factory service.
   *
   * @var \Drupal\va_gov_live_field_migration\Migrator\Factory\FactoryInterface
   */
  protected $migratorFactory;

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TranslationInterface $stringTranslation,
    ReporterInterface $reporter,
    FactoryInterface $migratorFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stringTranslation = $stringTranslation;
    $this->reporter = $reporter;
    $this->migratorFactory = $migratorFactory;
  }

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation'),
      $container->get('va_gov_live_field_migration.reporter'),
      $container->get('va_gov_live_field_migration.migrator_factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  abstract public function runMigration(string $entityType, string $fieldName);

  /**
   * {@inheritDoc}
   */
  abstract public function rollbackMigration(string $entityType, string $fieldName);

  /**
   * {@inheritDoc}
   */
  abstract public function verifyMigration(string $entityType, string $fieldName);

}
