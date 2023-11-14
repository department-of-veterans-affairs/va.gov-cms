<?php

namespace Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
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
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

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
    LoggerChannelFactoryInterface $loggerFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stringTranslation = $stringTranslation;
    $this->logger = $loggerFactory->get('va_gov_content_release');
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
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  abstract public function shouldTriggerContentRelease(VaNodeInterface $node) : bool;

  /**
   * {@inheritDoc}
   */
  abstract public function getReasonMessage(VaNodeInterface $node) : string;

}
