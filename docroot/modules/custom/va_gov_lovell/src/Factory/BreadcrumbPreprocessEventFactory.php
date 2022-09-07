<?php

namespace Drupal\va_gov_lovell\Factory;

use Drupal\preprocess_event_dispatcher\Event\AbstractPreprocessEvent;
use Drupal\preprocess_event_dispatcher\Factory\PreprocessEventFactoryInterface;
use Drupal\va_gov_lovell\Event\BreadcrumbPreprocessEvent;
use Drupal\va_gov_lovell\Variables\BreadcrumbEventVariables;

/**
 * Creates breadcrumb preprocess events and corresponding variables.
 */
class BreadcrumbPreprocessEventFactory implements PreprocessEventFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createEvent(array &$variables): AbstractPreprocessEvent {
    return new BreadcrumbPreprocessEvent(
      new BreadcrumbEventVariables($variables)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEventHook(): string {
    return BreadcrumbPreprocessEvent::getHook();
  }

}
