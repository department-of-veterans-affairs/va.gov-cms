<?php

namespace Drupal\va_gov_notifications\Event\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_notifications\Service\FlaggingInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets an edited flag for the specified user on a specified node.
 */
class EditedFlag implements EventSubscriberInterface {

  /**
   * The flag decisions service.
   *
   * @var \Drupal\va_gov_notifications\FlagDecisionsInterface
   */
  protected $flaggingService;

  /**
   * Constructor.
   */
  public function __construct(FlaggingInterface $flaggingService) {
    $this->flaggingService = $flaggingService;
  }

  /**
   * Inspect the specified entity and set the edited flag if appropriate.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity on which the flag should be set, if appropriate.
   */
  public function setEditedFlag(EntityInterface $entity): void {
    if ($entity instanceof NodeInterface) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $entity;
      $this->flaggingService->setEditedFlag($node, $node->getRevisionUser());
    }
  }

  /**
   * Entity insert.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $this->setEditedFlag($event->getEntity());
  }

  /**
   * Entity update.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $this->setEditedFlag($event->getEntity());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'entityInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'entityUpdate',
    ];
  }

}
