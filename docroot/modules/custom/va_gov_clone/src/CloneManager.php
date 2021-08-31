<?php

namespace Drupal\va_gov_clone;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_clone\Event\EntityCloneEvent;
use Drupal\entity_clone\Event\EntityCloneEvents;
use Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery;

/**
 * The Clone Manager to clone content.
 */
class CloneManager implements CloneManagerInterface {

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Clone Plugin Discovery.
   *
   * @var \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery
   */
  protected $cloneEntityFinderDiscovery;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor for CLone Manager.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event Dispatcher.
   * @param \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery $cloneEntityFinderDiscovery
   *   THe discovery for the clone entity finder plugins.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    EventDispatcherInterface $eventDispatcher,
    CloneEntityFinderDiscovery $cloneEntityFinderDiscovery,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->cloneEntityFinderDiscovery = $cloneEntityFinderDiscovery;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public function cloneEntity(EntityInterface $entity) : ?EntityInterface {
    /** @var \Drupal\entity_clone\EntityClone\EntityCloneInterface $entity_clone_handler */
    $entity_clone_handler = $this->entityTypeManager->getHandler($entity->getEntityTypeId(), 'entity_clone');

    $duplicate = $entity->createDuplicate();
    $this->eventDispatcher->dispatch(new EntityCloneEvent($entity, $duplicate), EntityCloneEvents::PRE_CLONE);
    $cloned_entity = $entity_clone_handler->cloneEntity($entity, $duplicate);
    $this->eventDispatcher->dispatch(new EntityCloneEvent($entity, $duplicate), EntityCloneEvents::POST_CLONE);
    return $cloned_entity;
  }

  /**
   * {@inheritDoc}
   */
  public function cloneEntities(array $entities) : void {
    foreach ($entities as $entity) {
      $this->cloneEntity($entity);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function cloneSection(int $section_id) : int {
    $total = 0;
    foreach ($this->cloneEntityFinderDiscovery->getDefinitions() as $plugin_name => $definition) {
      /** @var \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderInterface $cloneEntityFinder */
      $cloneEntityFinder = $this->cloneEntityFinderDiscovery->createInstance($plugin_name);
      $entities = $cloneEntityFinder->getEntitiesToClone($section_id);
      $total += count($entities);
      $this->cloneEntities($entities);
    }

    return $total;
  }

}
