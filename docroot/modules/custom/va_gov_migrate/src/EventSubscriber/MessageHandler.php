<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migration_tools\Event\MessageEvent;
use Drupal\migration_tools\Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handle message events.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class MessageHandler implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MessageEvent::EVENT_NAME] = 'onMessage';
    return $events;
  }

  /**
   * Outputs a csv for messages of ERROR or higher.
   *
   * @param \Drupal\migration_tools\Event\MessageEvent $event
   *   The event.
   */
  public function onMessage(MessageEvent $event) {
    if ($event->severity == Message::ERROR) {
      $handle = fopen("migrate_errors.csv", "a");
      fwrite($handle, '"' . $event->message . '",' . $event->type . "\n");
      fclose($handle);
    }
  }

}
