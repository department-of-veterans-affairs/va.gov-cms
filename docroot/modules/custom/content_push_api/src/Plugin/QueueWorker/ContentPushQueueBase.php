<?php

namespace Drupal\content_push_api\Plugin\QueueWorker;

use DateTime;
use Drupal\content_push_api\Service\Endpoint;
use Drupal\content_push_api\Service\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\slack\Slack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides base functionality for the ContentPush Queue Workers.
 */
abstract class ContentPushQueueBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * The endpoint.
   *
   * @var \Drupal\content_push_api\Service\Endpoint
   */
  protected $endpoint;

  /**
   * Request.
   *
   * @var \Drupal\content_push_api\Service\Request
   */
  protected $request;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Slack service.
   *
   * @var \Drupal\slack\Slack
   */
  protected $slack;

  /**
   * Creates a new ContentPushBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue Factory.
   * @param \Drupal\content_push_api\Service\Endpoint $endpoint
   *   Endpoint.
   * @param \Drupal\content_push_api\Service\Request $request
   *   Request.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Request.
   * @param \Drupal\slack\Slack $slack
   *   Slack service.
   */
  public function __construct(ConfigFactoryInterface $config, QueueFactory $queue, Endpoint $endpoint, Request $request, LoggerChannelFactoryInterface $logger, Slack $slack) {
    $this->config = $config;
    $this->queue = $queue;
    $this->endpoint = $endpoint;
    $this->request = $request;
    $this->logger = $logger;
    $this->slack = $slack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('queue'),
      $container->get('content_push_api.endpoint'),
      $container->get('content_push_api.request'),
      $container->get('logger.factory'),
      $container->get('slack.slack_service')
    );
  }

  /**
   * Processes items in the queue.
   *
   * @param \DateTime $end_time
   *   The timestamp (optional) to use for grabbing items created prior.
   */
  public function processQueue(DateTime $end_time = NULL) {
    // Call content push queue service, and create an instance for processing.
    $queue = $this->queue->get('content_push_queue');

    // Get the number of items.
    $number_in_queue = $queue->numberOfItems();
    // The queue will keep grabbing the same item after it is released, so
    // we need to grab them all and mass release them.
    $queued_items_to_release = [];

    for ($i = 0; $i < $number_in_queue; $i++) {
      // Get a queued item.
      // @TODO the release time should be close to the timeout time on
      // the Facility API.
      $item = $queue->claimItem(20);
      if ((!empty($item)) && (($end_time && $item->created < $end_time) || empty($end_time))) {
        // Now process individual item.
        $response = $this->processItem($item->data);
        if ($response === 200) {
          // API POST is a success - remove the processed item from the queue.
          $queue->deleteItem($item);
        }
        else {
          // Service not available - release the item.
          $queued_items_to_release[$item->item_id] = $item;
        }
      }
      else {
        // Item intentionally not processed - release for another time.
        if (!empty($item)) {
          $queued_items_to_release[$item->item_id] = $item;
        }
      }
    }

    // Processing of queue done, release all unprocessed items.
    foreach ($queued_items_to_release as $item_to_release) {
      $queue->releaseItem($item_to_release);
    }

    // If there's no change between input and output - send notification.
    if ($number_in_queue === count($queued_items_to_release)) {
      $this->logError($number_in_queue);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Send and return response.
    $endpoint_key = isset($data['endpoint_key']) ? $data['endpoint_key'] : NULL;
    $modifier = isset($data['modifier']) ? $data['modifier'] : NULL;
    $endpoint = $this->endpoint->endpoint($endpoint_key, $modifier);

    return $this->request->sendRequest($endpoint, $data['payload']);
  }

  /**
   * Logs queue processing error.
   *
   * @param int $queue_total
   *   Total number of items in queue.
   */
  protected function logError(int $queue_total) {
    $message = sprintf('Content Push API has failed to process queued items. Total items in queue: %d.', $queue_total);

    if ($this->config->get('content_push_api.settings')->get('slack')) {
      // @todo: proceed w/ message send only if
      $slack_config = $this->config->get('slack.settings');
      if ($slack_config->get('slack_webhook_url')) {
        $this->slack->sendMessage(':triangular_flag_on_post: ' . $message);
      }
      else {
        // @todo: use DI.
        \Drupal::messenger()->addWarning('Slack webhook is not set. Any error notifications will still be logged in dblog.');
      }
    }

    $this->logger->get('content_push_api')->error($message);
  }

}
