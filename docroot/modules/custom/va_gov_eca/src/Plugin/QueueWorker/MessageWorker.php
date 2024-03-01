<?php

declare(strict_types = 1);

namespace Drupal\va_gov_eca\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\message\Entity\Message;
use Drupal\message_notify\MessageNotifier;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'va_gov_eca_message_worker' queue worker.
 *
 * @QueueWorker(
 *   id = "va_gov_eca_message_worker",
 *   title = @Translation("Message worker"),
 *   cron = {"time" = 60},
 * )
 */
final class MessageWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new MessageWorker instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly MessageNotifier $messageNotifySender,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('message_notify.sender'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    // @todo Process data here.
    $this->messageNotifySender->send(new Message([]));
  }

}
