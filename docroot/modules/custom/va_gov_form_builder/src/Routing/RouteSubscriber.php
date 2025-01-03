<?php

namespace Drupal\va_gov_form_builder\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events to alter routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($collection->all() as $route_name => $route) {
      if (strpos($route_name, 'va_gov_form_builder.') === 0) {
        if (!$route->hasRequirement('_permission')) {
          $route->setRequirement('_permission', 'access form builder');
        }
      }
    }
  }

}
