<?php

namespace Drupal\va_gov_content_release\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens and responds to entity change events by (maybe) releasing content.
 */
interface EntityEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Handle entity insert events.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent $event
   *   The event object passed by the event dispatcher.
   */
  public function onInsert(AbstractEntityEvent $event) : void;

  /**
   * Handle entity update events.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent $event
   *   The event object passed by the event dispatcher.
   */
  public function onUpdate(AbstractEntityEvent $event) : void;

  /**
   * Handle entity delete events.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent $event
   *   The event object passed by the event dispatcher.
   */
  public function onDelete(AbstractEntityEvent $event) : void;

  /**
   * Handle entity updates for a specific node.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node to check for updates.
   */
  public function handleNodeUpdate(VaNodeInterface $node) : void;

}
