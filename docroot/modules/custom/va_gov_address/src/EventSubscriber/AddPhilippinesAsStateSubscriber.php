<?php

namespace Drupal\va_gov_address\EventSubscriber;

use Drupal\address\Event\AddressEvents;
use Drupal\address\Event\SubdivisionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds a Philippines to the US States.
 *
 * This class follows the Centarro guidelines:
 * https://docs.drupalcommerce.org/v2/developer-guide/customers/addresses/#how-do-i-add-or-modify-subdivisions-for-a-country.
 */
class AddPhilippinesAsStateSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AddressEvents::SUBDIVISIONS][] = ['onSubdivisions'];
    return $events;
  }

  /**
   * Provides the states of the US (plus the Philippines).
   *
   * @param \Drupal\address\Event\SubdivisionsEvent $event
   *   The subdivisions event.
   */
  public function onSubdivisions(SubdivisionsEvent $event) {
    if ($event->getParents() !== ['US']) {
      return;
    }

    $definitions = $event->getDefinitions();

    // Add the Philippines as a state.
    $definitions['subdivisions']['PH'] = [
      'code' => 'PH',
      'name' => 'Philippines',
      'country_code' => 'US',
      'id' => 'PH',
    ];
    ksort($definitions['subdivisions']);
    $event->setDefinitions($definitions);
  }

}
