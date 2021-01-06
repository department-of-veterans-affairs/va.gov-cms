<?php

namespace Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\va_gov_content_export\ExportCommand\CommandRunner;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AdvancedQueue Queue Plugin for web build.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_web_builder",
 *  label = @Translation("VA Web build queue"),
 *  max_retries = 0,
 *  retry_delay = 1
 * )
 */
class WebBuildJobType extends JobTypeBase implements ContainerFactoryPluginInterface {
  use CommandRunner;
  use LoggerChannelTrait;

  public const QUEUE_ID = 'va_gov_web_builder';

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
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger($this->getPluginId());
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    $this->logger->info('Starting front end rebuild.');

    $payload = $job->getPayload();

    $commands = $payload['commands'] ?? [];
    $messages = $this->runCommands($commands);

    if ($messages) {
      foreach ($messages as $message) {
        $this->logger->error($message);
      }

      return JobResult::failure();
    }

    $this->logger->info('Front end has been successfully rebuilt.');

    return JobResult::success();
  }

}
