<?php

namespace Drupal\va_gov_notifications\Plugin\Notifier;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\message_notify\Plugin\Notifier\MessageNotifierBase;
use Drupal\message\MessageInterface;
use Drupal\slack\Slack as SlackService;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Slack notifier for sending messages to Slack App.
 *
 * @Notifier(
 *   id = "slack",
 *   title = @Translation("Slack"),
 *   description = @Translation("Send messages via Slack"),
 *   viewModes = {
 *     "slack"
 *   }
 * )
 */
class Slack extends MessageNotifierBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Slack service.
   *
   * @var \Drupal\slack\Slack
   */
  protected $slackService;

  /**
   * Constructs the email notifier plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The message_notify logger channel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $render
   *   The rendering service.
   * @param \Drupal\slack\Slack $slack_service
   *   Slack service.
   * @param \Drupal\message\MessageInterface $message
   *   (optional) The message entity. This is required when sending or
   *   delivering a notification. If not passed to the constructor, use
   *   ::setMessage().
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LoggerChannelInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $render,
    SlackService $slack_service,
    MessageInterface $message = NULL
    ) {
    // Set configuration defaults.
    $configuration += [
      'mail' => FALSE,
      'language override' => FALSE,
      'from' => FALSE,
    ];
    $this->configFactory = $config_factory;
    $this->slackService = $slack_service;

    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $entity_type_manager, $render, $message);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MessageInterface $message = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.channel.message_notify'),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('slack.slack_service'),
      $message
    );
  }

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = []) {
    $success = FALSE;
    $channel = $this->message->slack_channel ?? NULL;
    $slack_user = $this->message->slack_user ?? NULL;
    $msg = $output['slack'] ?? '';

    $slack_config = $this->configFactory->get('slack.settings');
    if ($slack_config->get('slack_webhook_url')) {
      $result = $this->slackService->sendMessage($msg, $channel, $slack_user);
      $success = $this->checkSuccess($result);
    }
    else {
      // Safe fallback with some details for lower env that should not slack.
      $slack_disabled_message = 'Slack webhook is not set. Could not send notification.';
      $this->logger->error($slack_disabled_message);
    }

    return $success;
  }

  /**
   * Evaluates the success of the slack response.
   *
   * @param mixed $result
   *   A result object or empty value.
   *
   * @return bool
   *   TRUE if sent and received, FALSE otherwise.
   */
  private function checkSuccess($result) {
    $success = FALSE;
    if (!empty($result) && ($result instanceof ResponseInterface)) {
      if ($result->getStatusCode() === 200) {
        $success = TRUE;
      }
    }

    return $success;
  }

}
