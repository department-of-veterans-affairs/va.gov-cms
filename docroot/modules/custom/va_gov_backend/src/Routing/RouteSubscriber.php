<?php

namespace Drupal\va_gov_backend\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override /node/add page callback.
    if ($route = $collection->get('node.add_page')) {
      $route->setDefault('_controller', '\Drupal\va_gov_backend\Controller\VaGovBackendController::addPage');
    }
  }

}
