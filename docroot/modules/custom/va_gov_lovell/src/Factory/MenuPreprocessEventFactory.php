<?php

namespace Drupal\va_gov_lovell\Factory;

use Drupal\preprocess_event_dispatcher\Event\AbstractPreprocessEvent;
use Drupal\preprocess_event_dispatcher\Factory\PreprocessEventFactoryInterface;
use Drupal\va_gov_lovell\Event\MenuPreprocessEvent;
use Drupal\va_gov_lovell\Variables\MenuEventVariables;

/**
 * Creates menu preprocess events and corresponding variables.
 */
class MenuPreprocessEventFactory implements PreprocessEventFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createEvent(array &$variables): AbstractPreprocessEvent {
    return new MenuPreprocessEvent(
      new MenuEventVariables($variables)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEventHook(): string {
    return MenuPreprocessEvent::getHook();
  }

}
