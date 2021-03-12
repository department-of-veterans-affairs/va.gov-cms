<?php

namespace Drupal\va_gov_flags\EventSubscriber;

use Drupal\feature_toggle\Event\FeatureUpdateEvent;
use Drupal\feature_toggle\Event\FeatureUpdateEvents;
use Drupal\va_gov_flags\Export\ExportFeatureInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Feature Toggle Module Subscriber.
 */
class FeatureToggleSubscriber implements EventSubscriberInterface {

  /**
   * Export Feature service.
   *
   * @var \Drupal\va_gov_flags\Export\ExportFeatureInterface
   */
  protected $exportFeature;

  /**
   * FeatureToggleSubscriber constructor.
   *
   * @param \Drupal\va_gov_flags\Export\ExportFeatureInterface $exportFeature
   *   The export feature service.
   */
  public function __construct(ExportFeatureInterface $exportFeature) {
    $this->exportFeature = $exportFeature;
  }

  /**
   * Feature Toggle update toggle dispatcher.
   *
   * @param \Drupal\feature_toggle\Event\FeatureUpdateEvent $event
   *   The event object.
   */
  public function updateFeatureToggleDispatch(FeatureUpdateEvent $event) {
    $this->exportFeature->export();
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      FeatureUpdateEvents::UPDATE => 'updateFeatureToggleDispatch',
    ];
  }

}
