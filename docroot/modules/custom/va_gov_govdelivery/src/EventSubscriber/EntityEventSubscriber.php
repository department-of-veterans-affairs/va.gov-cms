<?php

namespace Drupal\va_gov_govdelivery\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_govdelivery\Service\ProcessStatusBulletin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * GovDelivery Entity Event Subscriber.
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
   *
   * @var \Drupal\va_gov_govdelivery\Service\ProcessStatusBulletin $process_status_bulletin
   *   The bulletin processing service.
   */
  public function __construct(ProcessStatusBulletin $process_status_bulletin) {
    $this->processStatusBulletin = $process_status_bulletin;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'handleEntityUpsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'handleEntityUpsert',
    ];
  }

  /**
   * React to entity insert/update events.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent $event
   *   The dispatched event.
   */
  public function handleEntityUpsert(AbstractEntityEvent $event) : void {
    $entity = $event->getEntity();
    if ($this->isStatusUpdateNode($entity)) {
      $this->processStatusBulletin->processNode($entity);
    }
  }

  /**
   * Determine if this is a status update node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being created or updated.
   *
   * @return bool
   *   Whether or not this is an status update node.
   */
  protected function isStatusUpdateNode(EntityInterface $entity) : bool {
    return ($entity instanceof NodeInterface) &&
      ($entity->getType() === 'full_width_banner_alert');
  }

}
