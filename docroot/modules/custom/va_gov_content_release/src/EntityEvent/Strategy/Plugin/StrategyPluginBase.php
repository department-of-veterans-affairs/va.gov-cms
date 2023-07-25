<?php

namespace Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class used for Entity Event Strategy plugins.
 *
 * These plugins are used to determine if a content release should be triggered
 * based on the environment and the entity event.
 */
abstract class StrategyPluginBase extends PluginBase implements StrategyPluginInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TranslationInterface $stringTranslation
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritDoc}
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
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritDoc}
   */
  abstract public function shouldTriggerContentRelease(VaNodeInterface $node) : bool;

}
