<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\node\NodeInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Backend Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->trimNodeTitleWhitespace($entity);
    }
  }

  /**
   * Trim any preceding and trailing whitespace on node titles.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to be modified.
   */
  private function trimNodeTitleWhitespace(NodeInterface $node) {
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
