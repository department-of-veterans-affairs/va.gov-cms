<?php

namespace Drupal\va_gov_live_field_migration\Migration\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
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
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ReporterInterface $reporter,
    TranslationInterface $stringTranslation
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->reporter = $reporter;
    $this->stringTranslation = $stringTranslation;
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
      $container->get('va_gov_live_field_migration.reporter'),
      $container->get('string_translation')
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
