<?php

namespace Drupal\va_gov_content_release\Strategy\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class used for Strategy plugins.
 */
abstract class StrategyPluginBase extends PluginBase implements StrategyPluginInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The reporter service.
   *
   * @var \Drupal\va_gov_content_release\Reporter\ReporterInterface
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
      $container->get('va_gov_content_release.reporter'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritDoc}
   */
  abstract public function triggerContentRelease() : void;

}
