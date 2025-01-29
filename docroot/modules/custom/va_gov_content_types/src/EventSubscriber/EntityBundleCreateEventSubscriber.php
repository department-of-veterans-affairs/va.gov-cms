<?php

namespace Drupal\va_gov_content_types\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityBundleCreateEvent;
use Drupal\prometheus_exporter\MetricsCollectorManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to EntityBundleCreateEvent events.
 *
 * We use this event to ensure that prometheus_exporter exports the node count
 * for newly-created bundles.
 */
class EntityBundleCreateEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[EntityHookEvents::ENTITY_BUNDLE_CREATE][] = ['onEntityBundleCreate'];
    return $events;
  }

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * The Prometheus Exporter metrics collector manager.
   *
   * @var \Drupal\prometheus_exporter\MetricsCollectorManager
   */
  private MetricsCollectorManager $metricsCollectorManager;

  /**
   * Constructor for EntityBundleEventSubscriber objects.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\prometheus_exporter\MetricsCollectorManager $metricsCollectorManager
   *   The Prometheus Exporter metrics collector manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory, MetricsCollectorManager $metricsCollectorManager) {
    $this->configFactory = $configFactory;
    $this->metricsCollectorManager = $metricsCollectorManager;
  }

  /**
   * Respond to the entity_bundle_create event.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityBundleCreateEvent $event
   *   The event to process.
   */
  public function onEntityBundleCreate(EntityBundleCreateEvent $event): void {
    $entityTypeId = $event->getEntityTypeId();
    if ($entityTypeId !== 'node') {
      return;
    }
    $this->enableNodeCountOnContentType($event->getBundle());
  }

  /**
   * Enable node_count for the specified content type.
   *
   * @param string $contentType
   *   The content type to enable.
   */
  public function enableNodeCountOnContentType(string $contentType): void {
    $config = $this->configFactory->getEditable('prometheus_exporter.settings');
    $bundles = $config->get('collectors.node_count.settings.bundles') ?? [];
    $bundles[$contentType] = $contentType;
    $config->set('collectors.node_count.settings.bundles', $bundles);
    $config->save();
    $this->metricsCollectorManager->syncPluginConfig();
  }

}
