<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\node\Entity\Node;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Backend Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity create Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node') {
      $this->trimNodeTitleWhitespace($entity);
    }
  }

  /**
   * Trim any preceding and trailing whitespace on node titles.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to be modified.
   */
  private function trimNodeTitleWhitespace(Node $node) {
    $title = $node->getTitle();
    // Trim leading and then trailing separately to avoid a convoluted regex.
    $title = preg_replace('/^\s+/', '', $title);
    $title = preg_replace('/\s+$/', '', $title);
    $node->setTitle($title);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'entityPresave',
    ];
  }

}
