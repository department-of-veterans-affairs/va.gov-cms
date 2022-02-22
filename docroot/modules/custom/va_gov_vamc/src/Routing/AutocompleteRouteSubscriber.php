<?php

namespace Drupal\va_gov_vamc\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Autocomplete route subscriber.
 */
class AutocompleteRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.entity_autocomplete')) {
      $route->setDefault('_controller', '\Drupal\va_gov_vamc\Controller\EntityAutocompleteController::handleAutocomplete');
    }
  }

}
