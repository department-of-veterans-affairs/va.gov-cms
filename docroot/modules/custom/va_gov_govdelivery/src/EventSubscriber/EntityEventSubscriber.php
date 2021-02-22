<?php

namespace Drupal\va_gov_govdelivery\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\node\NodeInterface;
use Drupal\va_gov_govdelivery\Service\ProcessStatusBulletin;

/**
 * Class EntityEventSubscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Status bulletin processing service.
   *
   * @var \Drupal\va_gov_govdelivery\Service\ProcessStatusBulletin
   */
  protected $processStatusBulletin;

  /**
   * Constructs a new EventSubscriber object.
   */
  public function __construct(ProcessStatusBulletin $process_status_bulletin) {
    $this->processStatusBulletin = $process_status_bulletin;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    $events['HookEventDispatcherInterface::ENTITY_INSERT'] = ['processStatusBulletins'];
    $events['HookEventDispatcherInterface::ENTITY_UPDATE'] = ['processStatusBulletins'];

    return $events;
  }

  /**
   * React to entity inserts.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function processStatusBulletins(Event $event) : void {
    if ($this->isStatusUpdateNode($event->entity)) {
      $this->processStatusBulletin->processNode($event->entity);
    }
  }

  /**
   * Determine if this is a status update node.
   *
   * @param EntityInterface $entity
   *   The entity being created or updated.
   *
   * @return bool
   *   Whether or not this is an status update node.
   */
  protected function isStatusUpdateNode(EntityInterface $entity) : bool {
    return $entity instanceof NodeInterface &&
      $entity->getType() === 'full_width_banner_alert';
  }

}
