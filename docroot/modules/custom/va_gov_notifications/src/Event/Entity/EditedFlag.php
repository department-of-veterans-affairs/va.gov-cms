<?php

namespace Drupal\va_gov_notifications\Event\Entity;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\flag\FlagServiceInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_notifications\Service\FlagDecisionsInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets an edited flag for the specified user on a specified node.
 */
class EditedFlag implements EventSubscriberInterface {

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * The flag decisions service.
   *
   * @var \Drupal\va_gov_notifications\FlagDecisionsInterface
   */
  protected $flagDecisions;

  /**
   * Constructor.
   */
  public function __construct(
    FlagServiceInterface $flagService,
    FlagDecisionsInterface $flagDecisions
  ) {
    $this->flagService = $flagService;
    $this->flagDecisions = $flagDecisions;
  }

  /**
   * Sets `edited` flag for the revision user for the specified node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node on which the flag should be set.
   */
  public function setEditedFlag(NodeInterface $node): void {
    $user = $node->getRevisionUser();
    if ($this->flagDecisions->shouldSetEditedFlag($node, $user)) {
      $flag = $this->flagService->getFlagById('edited');
      $this->flagService->flag($flag, $node, $user);
    }
  }

  /**
   * Entity insert.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $entity;
      $this->setEditedFlag($node);
    }
  }

  /**
   * Entity update.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $entity;
      $this->setEditedFlag($node);
    }
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
