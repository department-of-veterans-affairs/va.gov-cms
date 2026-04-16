<?php

namespace Drupal\va_gov_workflow\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\va_gov_workflow\Service\Flagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Workflow Migrate Event Subscriber.
 */
class MigrateEventSubscriber implements EventSubscriberInterface {

  /**
   * The flagger service.
   *
   * @var \Drupal\va_gov_workflow\Service\Flagger
   */
  protected $flagger;

  /**
   * {@inheritdoc}
   */
  public function __construct(Flagger $flagger) {
    $this->flagger = $flagger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PRE_ROW_SAVE] = 'preRowSave';
    $events[MigrateEvents::POST_ROW_SAVE] = 'postRowSave';
    return $events;
  }

  /**
   * Pre row save event call.
   *
   * @param \Drupal\migrate\Event\MigratePreRowSaveEvent $event
   *   The migrate import event.
   */
  public function preRowSave(MigratePreRowSaveEvent $event) {
    // Flag is already set.
    if ($this->flagger->isMigrating()) {
      return;
    }
    $this->flagger->setMigrating(TRUE);
  }

  /**
   * Post row save event call.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The migrate import event.
   */
  public function postRowSave(MigratePostRowSaveEvent $event) {
    $this->flagger->setMigrating(FALSE);
  }

}
