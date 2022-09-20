<?php

namespace Drupal\va_gov_build_trigger\EventSubscriber;

use Drupal\prometheus_exporter\MetricsCollectorManager;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Recalculates metrics when a release has fully completed.
 */
class ContentReleaseMetricsRecalculationSubscriber implements EventSubscriberInterface {

  /**
   * The metrics collector.
   *
   * @var \Drupal\prometheus_exporter\MetricsCollectorManager
   */
  protected $metricsCollector;

  /**
   * Constructs a new ContentReleaseMetricsRecalculationSubscriber object.
   */
  public function __construct(MetricsCollectorManager $metricsCollector) {
    $this->metricsCollector = $metricsCollector;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ReleaseStateTransitionEvent::NAME] = 'recalculateMetrics';
    return $events;
  }

  /**
   * Recalculate metrics when the release is transitioned back to ready.
   *
   * @param \Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent $event
   *   The release state transition event.
   */
  public function recalculateMetrics(ReleaseStateTransitionEvent $event) {
    if ($event->getNewReleaseState() === ReleaseStateManager::STATE_READY) {
      // We don't need to do anything with these metrics. The act of collecting
      // them recalculates them, which is all that needs to happen here. We need
      // to collect them twice because some of the metrics depend on values from
      // other calculated metrics.
      $this->metricsCollector->collectMetrics();
      $this->metricsCollector->collectMetrics();
    }
  }

}
