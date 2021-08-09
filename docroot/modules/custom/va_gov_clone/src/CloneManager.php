<?php

namespace Drupal\va_gov_clone;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_clone\EntityClone\EntityCloneInterface;
use Drupal\entity_clone\Event\EntityCloneEvent;
use Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The Clone Manager to clone content.
 */
class CloneManager implements CloneManagerInterface {

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * EntityClone Handler.
   *
   * @var \Drupal\entity_clone\EntityClone\EntityCloneInterface
   */
  protected $entityCloneHandler;

  /**
   * Clone Plugin Discovery.
   *
   * @var \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery
   */
  protected $cloneEntityFinderDiscovery;

  /**
   * Constructor for CLone Manager.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event Dispatcher.
   * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $entityCloneHandler
   *   The entity Clone Handlers.
   * @param \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery $cloneEntityFinderDiscovery
   *   THe discovery for the clone entity finder plugins.
   */
  public function __construct(
    EventDispatcherInterface $eventDispatcher,
    EntityCloneInterface $entityCloneHandler,
    CloneEntityFinderDiscovery $cloneEntityFinderDiscovery
  ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->entityCloneHandler = $entityCloneHandler;
    $this->cloneEntityFinderDiscovery = $cloneEntityFinderDiscovery;
  }

  /**
   * {@inheritDoc}
   */
  public function cloneEntity(EntityInterface $entity) : ?EntityInterface {
    $duplicate = $entity->createDuplicate();
    $this->eventDispatcher->dispatch(new EntityCloneEvent($entity, $duplicate));
    return $this->entityCloneHandler->cloneEntity($entity, $duplicate);
  }

  /**
   * {@inheritDoc}
   */
  public function cloneEntities(array $nodes) : void {
    foreach ($nodes as $node) {
      $this->cloneEntity($node);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function cloneAll(int $office_tid) : int {
    $total = 0;
    foreach ($this->cloneEntityFinderDiscovery->getDefinitions() as $plugin_name => $definition) {
      /** @var \Drupal\va_gov_clone\CloneHandler\CloneEntityFinderInterface $cloneEntityFinder */
      $cloneEntityFinder = $this->cloneEntityFinderDiscovery->createInstance($plugin_name);
      $entities = $cloneEntityFinder->getEntitiesToClone($office_tid);
      $total += count($entities);
      $this->cloneEntities($entities);
    }

    return $total;
  }

}
