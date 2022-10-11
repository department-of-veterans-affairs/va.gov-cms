<?php

namespace Drupal\va_gov_lovell\Event;

use Drupal\preprocess_event_dispatcher\Event\AbstractPreprocessEvent;

/**
 * Represents a breadcrumb preprocess event.
 */
class BreadcrumbPreprocessEvent extends AbstractPreprocessEvent {

  /**
   * {@inheritdoc}
   */
  public static function getHook(): string {
    return 'breadcrumb';
  }

}
