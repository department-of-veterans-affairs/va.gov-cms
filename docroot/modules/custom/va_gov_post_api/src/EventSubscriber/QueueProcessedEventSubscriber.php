<?php

namespace Drupal\va_gov_post_api\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\post_api\Event\QueueProcessingCompleteEvent;
use Drupal\slack\Slack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QueueProcessedEventSubscriber.
 *
 * @package Drupal\va_gov_post_api\EventSubscriber
 */
class QueueProcessedEventSubscriber implements EventSubscriberInterface, ContainerFactoryPluginInterface {

  use MessengerTrait;

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
   * Creates a new QueueProcessedEventSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param \Drupal\slack\Slack $slack
   *   Slack service.
   */
  public function __construct(ConfigFactoryInterface $config, LoggerChannelFactoryInterface $logger, ModuleHandlerInterface $moduleHandler, Slack $slack) {
    $this->config = $config;
    $this->logger = $logger;
    $this->moduleHandler = $moduleHandler;
    $this->slack = $slack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('module_handler'),
      $container->get('slack.slack_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      QueueProcessingCompleteEvent::EVENT_NAME => 'onQueueProcessed',
    ];
  }

  /**
   * React to the queue event dispatched.
   *
   * @param \Drupal\post_api\Event\QueueProcessingCompleteEvent $event
   *   Event object.
   */
  public function onQueueProcessed(QueueProcessingCompleteEvent $event) {
    $message = NULL;
    $start = $event->getItemsInQueueStart();
    $finish = $event->getItemsInQueueFinish();
    if ($start === $finish) {
      $message = sprintf('Post API has failed to process queued items. Total items in queue: %d.', $finish);
    }

    if ($this->moduleHandler->moduleExists('slack')) {
      $slack_config = $this->config->get('slack.settings');
      if ($slack_config->get('slack_webhook_url')) {
        $this->slack->sendMessage(':triangular_flag_on_post: ' . $message);
      }
      else {
        $slack_disabled_message = 'Slack webhook is not set. All notifications will still be logged in dblog.';
        $this->messenger()->addWarning($slack_disabled_message);
        $this->logger->get('va_gov_post_api')->info($slack_disabled_message);
      }
    }

  }

}
