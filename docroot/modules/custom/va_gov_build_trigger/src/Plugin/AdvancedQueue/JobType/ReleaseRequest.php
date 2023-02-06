<?php

namespace Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Entity\QueueInterface;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AdvancedQueue queue processor plugin for content release requests.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_content_release_request",
 *  label = @Translation("VA.gov Content Release Request"),
 *  max_retries = 30,
 *  retry_delay = 120
 * )
 */
class ReleaseRequest extends JobTypeBase implements ContainerFactoryPluginInterface {
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
   * The content release dispatch queue.
   *
   * @var \Drupal\advancedqueue\Entity\QueueInterface
   */
  protected $dispatchQueue;

  /**
   * Constructs a ReleaseRequest object.
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
   *   The release state manager.
   * @param \Drupal\advancedqueue\Entity\QueueInterface $dispatchQueue
   *   The release dispatch queue to add dispatch jobs to.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    ReleaseStateManagerInterface $releaseStateManager,
    QueueInterface $dispatchQueue
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger('va_gov_build_trigger');
    $this->releaseStateManager = $releaseStateManager;
    $this->dispatchQueue = $dispatchQueue;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $dispatchQueue = $container->get('entity_type.manager')
      ->getStorage('advancedqueue_queue')
      ->load('content_release');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('va_gov_build_trigger.release_state_manager'),
      $dispatchQueue,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    switch ($this->releaseStateManager->canAdvanceStateTo(ReleaseStateManager::STATE_REQUESTED)) {
      case ReleaseStateManager::STATE_TRANSITION_OK:
        $payload = $job->getPayload();
        $dispatch_job = Job::create('va_gov_content_release_dispatch', ['placeholder' => 'placeholder']);
        $this->dispatchQueue->enqueueJob($dispatch_job);
        $this->releaseStateManager->advanceStateTo(ReleaseStateManager::STATE_REQUESTED);
        $message = 'Content release dispatch has been queued. Reason: @reason';
        $this->logger->info($message, [
          '@reason' => $payload['reason'],
        ]);
        return JobResult::success($message);

      case ReleaseStateManager::STATE_TRANSITION_WAIT:
        $message = 'Release request will be processed when the current release is completed. Current changes may not be included in the current release, but will go live during the next release. Will retry request in two minutes.';
        $this->logger->info($message);
        // Since this job has a default retry behavior, this will be re-queued.
        return JobResult::failure($message);

      case ReleaseStateManager::STATE_TRANSITION_SKIP:
        $message = 'Release request will be included in the upcoming release dispatch. An additional release dispatch has <em>not<em> been queued.';
        $this->logger->info($message);
        return JobResult::success($message);

      case ReleaseStateManager::STATE_TRANSITION_INVALID:
        $message = 'Somehow, ReleaseStateManager has determined that requesting a release right now is invalid.';
        $this->logger->info($message);
        return JobResult::failure($message, 0);
    }
  }

}
