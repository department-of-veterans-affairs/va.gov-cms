<?php

namespace Drupal\va_gov_content_release\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\AbstractEntityEvent;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginManagerInterface;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\ResolverInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * Listens and responds to entity change events by (maybe) releasing content.
 */
class EntityEventSubscriber implements EntityEventSubscriberInterface {

  /**
   * The strategy plugin manager.
   *
   * @var \Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginManagerInterface
   */
  protected $strategyPluginManager;

  /**
   * The strategy resolver.
   *
   * @var \Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\ResolverInterface
   */
  protected $strategyResolver;

  /**
   * The content release request service.
   *
   * @var \Drupal\va_gov_content_release\Request\RequestInterface
   */
  protected $request;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginManagerInterface $strategyPluginManager
   *   The strategy plugin manager.
   * @param \Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\ResolverInterface $strategyResolver
   *   The strategy resolver.
   * @param \Drupal\va_gov_content_release\Request\RequestInterface $request
   *   The content release request service.
   */
  public function __construct(
    StrategyPluginManagerInterface $strategyPluginManager,
    ResolverInterface $strategyResolver,
    RequestInterface $request
  ) {
    $this->strategyPluginManager = $strategyPluginManager;
    $this->strategyResolver = $strategyResolver;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'onInsert',
      EntityHookEvents::ENTITY_UPDATE => 'onUpdate',
      EntityHookEvents::ENTITY_DELETE => 'onDelete',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function onInsert(AbstractEntityEvent $event) : void {
    $entity = $event->getEntity();
    if ($entity instanceof VaNodeInterface) {
      $this->handleNodeUpdate($entity);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function onUpdate(AbstractEntityEvent $event) : void {
    $entity = $event->getEntity();
    if ($entity instanceof VaNodeInterface) {
      $this->handleNodeUpdate($entity);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function onDelete(AbstractEntityEvent $event) : void {
    $entity = $event->getEntity();
    if ($entity instanceof VaNodeInterface) {
      $this->handleNodeUpdate($entity);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function handleNodeUpdate(VaNodeInterface $node) : void {
    $strategyId = $this->strategyResolver->getStrategyId();
    $strategy = $this->strategyPluginManager->getStrategy($strategyId);
    if ($strategy->shouldTriggerContentRelease($node)) {
      $reason = $strategy->getReasonMessage($node);
      $this->request->submitRequest($reason);
    }
  }

}
