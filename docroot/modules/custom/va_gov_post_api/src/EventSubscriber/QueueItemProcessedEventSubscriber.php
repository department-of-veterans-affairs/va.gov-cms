<?php

namespace Drupal\va_gov_post_api\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\post_api\Event\QueueItemProcessedEvent;
use Drupal\post_api\Service\AddToQueue;
use Drupal\slack\Slack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Acts on post_api queue item processed.
 *
 * @package Drupal\va_gov_post_api\EventSubscriber
 */
class QueueItemProcessedEventSubscriber implements EventSubscriberInterface, ContainerFactoryPluginInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Slack service.
   *
   * @var \Drupal\slack\Slack
   */
  protected $slack;

  /**
   * The Post queue add service.
   *
   * @var \Drupal\post_api\Service\AddToQueue
   */
  protected $postQueue;

  /**
   * Creates a new QueueItemProcessedEventSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param \Drupal\slack\Slack $slack
   *   Slack service.
   * @param \Drupal\post_api\Service\AddToQueue $post_queue
   *   The PostAPI service.
   */
  public function __construct(ConfigFactoryInterface $config, LoggerChannelFactoryInterface $logger, ModuleHandlerInterface $moduleHandler, Slack $slack, AddToQueue $post_queue) {
    $this->config = $config;
    $this->logger = $logger;
    $this->moduleHandler = $moduleHandler;
    $this->slack = $slack;
    $this->postQueue = $post_queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('module_handler'),
      $container->get('slack.slack_service'),
      $container->get('post_api.add_to_queue'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      QueueItemProcessedEvent::EVENT_NAME => 'onQueueItemProcessed',
    ];
  }

  /**
   * React to the "queue item processed" event dispatched.
   *
   * @param \Drupal\post_api\Event\QueueItemProcessedEvent $event
   *   Event object.
   */
  public function onQueueItemProcessed(QueueItemProcessedEvent $event) {
    $response_code = $event->getResponseCode();
    $response_phrase = $event->getReasonPhrase();
    $size = $event->getResponseSize();

    $item_data = $event->getQueueItem();
    $facility_id = str_replace('facility_status_', '', $item_data['uid']);
    if ($response_code === 200) {
      if ($response_phrase !== 'OK' || $size > 0) {
        // The response might have been 200 from the TIC not the Facility API.
        $message = sprintf('Item %s Posted with a 200, but had an unexpected response with a size: %F phrase: %s', $item_data['uid'], $size, $response_phrase);
        $this->logger->get('va_gov_post_api')->warning($message);

        if ($this->moduleHandler->moduleExists('slack')) {
          $slack_config = $this->config->get('slack.settings');
          if ($slack_config->get('slack_webhook_url')) {
            $this->slack->sendMessage(':warning: ' . $message);
          }
        }

        // Add the item that might not have been processed back to the queue.
        $this->postQueue->addToQueue($item_data);
      }
    }
    elseif (in_array($response_code, [201, 202])) {
      // The post worked, but was not as expected.
      $message = sprintf('Post API: facility %s doesn\'t exist and was removed from queue. POST response code - %d. ENV: %s.', $facility_id, $response_code, getenv('CMS_ENVIRONMENT_TYPE'));
      $this->logger->get('va_gov_post_api')->warning($message);

      if ($this->moduleHandler->moduleExists('slack')) {
        $slack_config = $this->config->get('slack.settings');
        if ($slack_config->get('slack_webhook_url')) {
          $this->slack->sendMessage(':warning: ' . $message);
        }
      }
    }

  }

}
