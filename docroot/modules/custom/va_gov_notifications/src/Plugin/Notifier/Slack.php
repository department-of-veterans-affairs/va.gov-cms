<?php

namespace Drupal\message_notify\Plugin\Notifier;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\message\MessageInterface;
use Drupal\message_notify\Exception\MessageNotifyException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Email notifier.
 *
 * @Notifier(
 *   id = "slack",
 *   title = @Translation("Slack"),
 *   description = @Translation("Send messages via Slack"),
 *   viewModes = {
 *     "slack_body"
 *   }
 * )
 */
class Slack extends MessageNotifierBase {

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs the email notifier plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The message_notify logger channel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $render
   *   The rendering service.
   * @param \Drupal\message\MessageInterface $message
   *   (optional) The message entity. This is required when sending or
   *   delivering a notification. If not passed to the constructor, use
   *   ::setMessage().
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger, EntityTypeManagerInterface $entity_type_manager, RendererInterface $render, MessageInterface $message = NULL, MailManagerInterface $mail_manager) {
    // Set configuration defaults.
    $configuration += [
      'mail' => FALSE,
      'language override' => FALSE,
      'from' => FALSE,
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $entity_type_manager, $render, $message);

    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MessageInterface $message = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.message_notify'),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $message,
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function deliver(array $output = []) {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->message->getOwner();

    if (!$this->configuration['mail'] && !$account->id()) {
      // The message has no owner and no mail was passed. This will cause an
      // exception, we just make sure it's a clear one.
      throw new MessageNotifyException('It is not possible to send a Message to an anonymous owner. You may set an owner using ::setOwner() or pass a "mail" to the $options array.');
    }

    $mail = $this->configuration['mail'] ?: $account->getEmail();
    $from = $this->configuration['from'] ?: NULL;

    if (!$this->configuration['language override']) {
      $language = $account->getPreferredLangcode();
    }
    else {
      $language = $this->message->language()->getId();
    }

    // The subject in an email can't be with HTML, so strip it.
    $output['mail_subject'] = trim(strip_tags($output['mail_subject']));

    // Pass the message entity along to hook_drupal_mail().
    $output['message_entity'] = $this->message;

    $result = $this->mailManager->mail(
      'message_notify',
      $this->message->getTemplate()->id(),
      $mail,
      $language,
      $output,
      $from
    );

    return $result['result'];
  }

}
