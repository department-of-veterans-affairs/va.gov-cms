<?php

namespace Drupal\va_gov_build_trigger\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_build_trigger\Service\BuildFrontend;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Build Trigger Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * The BuildFrontend service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildFrontend
   */
  protected $buildFrontend;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_build_trigger\Service\BuildFrontend $build_frontend_service
   *   The build front end service.
   */
  public function __construct(
    BuildFrontend $build_frontend_service
  ) {
    $this->buildFrontend = $build_frontend_service;
  }

  /**
   * Entity delete Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event.
   */
  public function entityDelete(EntityDeleteEvent $event): void {
    // Do some fancy stuff with dieing entity.
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->buildFrontend->triggerFrontendBuildFromContentSave($entity);
    }
  }

  /**
   * Entity insert Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    // Do some fancy stuff with new entity.
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->buildFrontend->triggerFrontendBuildFromContentSave($entity);
    }
  }

  /**
   * Entity update Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    // Do some fancy stuff with new entity.
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->buildFrontend->triggerFrontendBuildFromContentSave($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_DELETE => 'entityDelete',
      HookEventDispatcherInterface::ENTITY_INSERT => 'entityInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'entityUpdate',
    ];
  }

}
