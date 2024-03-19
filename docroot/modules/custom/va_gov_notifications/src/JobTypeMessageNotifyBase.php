<?php

namespace Drupal\va_gov_notifications;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\message\Entity\Message;
use Drupal\message_notify\MessageNotifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class for all VA.gov AdvancedQueue job types.
 */
class JobTypeMessageNotifyBase extends JobTypeBase implements ContainerFactoryPluginInterface, JobTypeMessageNotifyBaseInterface {

  use LoggerChannelTrait;

  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The message_notify.sender service.
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  protected MessageNotifier $messageNotifier;

  /**
   * The active job.
   *
   * @var \Drupal\advancedqueue\Job
   */
  protected Job $job;

  /**
   * Constructs a JobTypeMessageNotifyBase object.
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
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LoggerChannelFactoryInterface $loggerFactory, MessageNotifier $messageNotifier) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger('va_gov_notifications');
    $this->messageNotifier = $messageNotifier;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) :static {
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
  public function process(Job $job): JobResult {
    $this->job = $job;
    $message = $this->createMessage($this->job->getPayload());
    if (!$this->allowedToSend($message, $this->job->getPayload())) {
      return JobResult::failure($this->getRestrictedRecipientMessage(), 0);
    }
    $status = $this->messageNotifier->send($message);
    if (!$status) {
      $error_message = $this->getErrorMessage($job, $message);
      $this->logger->error($error_message);
      return JobResult::failure($error_message);
    }
    $success_message = $this->getSuccessMessage($job, $message);
    $this->logger->info($success_message);
    return JobResult::success($success_message);
  }

  /**
   * Creates a notification Message entity from a Job's payload.
   *
   * @param array $payload
   *   The current job payload.
   * @param bool $save
   *   TRUE to save and persist the message in the database.
   *
   * @return \Drupal\message\Entity\Message
   *   The newly created Message entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown when the Message entity cannot be saved.
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   Thrown when the template_values array key is missing from the payload.
   */
  public function createMessage(array $payload, bool $save = TRUE): Message {
    if (empty($payload['template_values'])) {
      throw new MissingDataException(sprintf('Missing template_values in payload for job id %s', $this->job->getId()));
    }
    $message = Message::create($payload['template_values']);
    $this->populateMessage($message, $payload);
    if ($save) {
      $message->save();
    }
    return $message;
  }

  /**
   * Populates a Message with values from the Job's payload.
   *
   * @param \Drupal\message\Entity\Message $message
   *   The current message entity.
   * @param array $payload
   *   The current payload values.
   */
  public function populateMessage(Message $message, array $payload): void {
    foreach ($payload['values'] as $field_name => $value) {
      $message->set($field_name, $value);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getErrorMessage(Job $job, Message $message): string {
    return "Failed to send message {$message->id()}.";
  }

  /**
   * {@inheritDoc}
   */
  public function getSuccessMessage(Job $job, Message $message): string {
    return "Message {$message->id()} sent successfully.";
  }

  /**
   * {@inheritDoc}
   */
  public function getRestrictedRecipientMessage(Job $job, Message $message): string {
    return "Recipient is not on the allow list for message {$message->id()}.";
  }

  /**
   * {@inheritDoc}
   */
  public function allowedToSend(Message $message, array $payload): bool {
    if (!isset($payload['restrict_delivery_to'])) {
      return FALSE;
    }
    $allowed_recipients = (array) $payload['restrict_delivery_to'];
    return in_array($message->getOwnerId(), $allowed_recipients);
  }

}
