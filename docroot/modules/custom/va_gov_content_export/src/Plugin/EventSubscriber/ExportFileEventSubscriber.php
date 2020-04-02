<?php


namespace Drupal\va_gov_content_export\Plugin\EventSubscriber;


use Drupal\tome_sync\Event\TomeSyncEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportFileEventSubscriber implements EventSubscriberInterface{

  public function exportAllContent() {

  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[TomeSyncEvents::EXPORT_ALL] = ['exportAllContent'];
  }

}
