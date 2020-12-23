<?php

namespace Drupal\va_gov_build_trigger\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_content_export\ExportCommand\CommandRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "va_gov_web_builder",
 *   title = @Translation("Frontend Build Worker")
 * )
 */
class WebBuildQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use CommandRunner;

  public const QUEUE_NAME = 'va_gov_web_builder';

  /**
   * The Environment Discovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param EnvironmentDiscovery $environmentDiscovery
   *   The Environment discovery service.
   * @param LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EnvironmentDiscovery $environmentDiscovery, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->environmentDiscovery = $environmentDiscovery;
    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger(static::QUEUE_NAME);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('va_gov.build_trigger.environment_discovery'),
      $container->get('logger.factory')
    );
  }


  /**
   * {@inheritDoc}
   */
  public function processItem($data) {
    $commands = $data ?? [];

    $messages = $this->runCommands($commands);
    foreach ($messages as $message) {
      $this->logger->error($message);
    }

    $this->logger->info('Front end has been rebuilt');
  }

}
