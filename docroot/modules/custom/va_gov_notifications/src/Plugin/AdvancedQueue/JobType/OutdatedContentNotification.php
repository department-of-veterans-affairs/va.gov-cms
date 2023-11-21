<?php

namespace Drupal\va_gov_notifications\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\message\Entity\Message;
use Drupal\message_notify\MessageNotifier;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AdvancedQueue queue processor plugin for outdated content notifications.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_outdated_content_notification",
 *  label = @Translation("VA.gov Outdated Content Notification"),
 *  max_retries = 3,
 *  retry_delay = 10
 * )
 */
class OutdatedContentNotification extends JobTypeBase implements ContainerFactoryPluginInterface {
  use LoggerChannelTrait;

  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The message_notify.sender service.
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  protected $messageNotifier;

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
   * @param \Drupal\message_notify\MessageNotifier $messageNotifier
   *   The message notifier.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    MessageNotifier $messageNotifier
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger('va_gov_notifications');
    $this->messageNotifier = $messageNotifier;
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
      $container->get('message_notify.sender')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    $payload = $job->getPayload();
    $notification = Message::create($payload['template_values']);
    foreach ($payload['values'] as $field_name => $value) {
      $notification->set($field_name, $value);
    }
    $status = $this->messageNotifier->send($notification);
    if (!$status) {
      $message = "Failed to send message {$notification->id()} to {$payload['values']['field_editor_username']}.";
      $this->logger->error($message);
      return JobResult::failure($message);
    }
    $message = "Message {$notification->id()} sent successfully to {$payload['values']['field_editor_username']}.";
    $this->logger->info($message);
    return JobResult::success($message);
  }

}
