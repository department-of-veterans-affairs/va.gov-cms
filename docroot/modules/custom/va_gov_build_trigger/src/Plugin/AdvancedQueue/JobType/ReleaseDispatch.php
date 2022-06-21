<?php

namespace Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;

/**
 * AdvancedQueue queue processor plugin for content release dispatch.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_content_release_dispatch",
 *  label = @Translation("VA.gov Content Release Dispatch"),
 *  max_retries = 30,
 *  retry_delay = 120
 * )
 */
class ReleaseDispatch extends JobTypeBase implements ContainerFactoryPluginInterface {
  use LoggerChannelTrait;

  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The release state manager.
   *
   * @var \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface
   */
  protected $releaseStateManager;

  /**
   * The environment discovery service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin ID.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory.
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   The release manager service.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   The environment discovery service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    ReleaseStateManagerInterface $releaseStateManager,
    EnvironmentDiscovery $environmentDiscovery
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger('va_gov_build_trigger');
    $this->releaseStateManager = $releaseStateManager;
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('va_gov_build_trigger.release_state_manager'),
      $container->get('va_gov.build_trigger.environment_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    switch ($this->releaseStateManager->canAdvanceStateTo(ReleaseStateManager::STATE_DISPATCHED)) {
      case ReleaseStateManager::STATE_TRANSITION_OK:
        try {
          $this->environmentDiscovery->triggerFrontendBuild();
        }
        catch (\Exception $e) {
          return JobResult::failure('Release dispatch failed with error: ' . $e->getMessage());
        }
        $this->releaseStateManager->advanceStateTo(ReleaseStateManager::STATE_DISPATCHED);
        $message = 'Content release has been dispatched.';
        $this->logger->info($message);
        return JobResult::success($message);

      case ReleaseStateManager::STATE_TRANSITION_WAIT:
        $message = 'Release dispatch cannot be processed right now. Will try again in two minutes.';
        $this->logger->info($message);
        // Since this job has a default retry behavior, this will be re-queued.
        return JobResult::failure($message);

      case ReleaseStateManager::STATE_TRANSITION_SKIP:
        $message = 'A release has already been dispatched but has not started yet. An additional release has <em>not</em> been dispatched.';
        $this->logger->info($message);
        return JobResult::success($message);

      case ReleaseStateManager::STATE_TRANSITION_INVALID:
        $message = 'Dispatching a new release cannot happen right now. This request will be ignored.';
        $this->logger->info($message);
        return JobResult::failure($message, 0);
    }
  }

}
