<?php

namespace Drupal\va_gov_govdelivery\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * GraphQL endpoint route alter.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    $route_provider = \Drupal::service('router.route_provider');
    $graphql_routes = $route_provider->getRoutesByPattern('api/govdelivery_bulletins/queue');
    $graphql_iterator = $graphql_routes->getIterator();

    foreach ($graphql_iterator as $route_name => $route_params) {
      if ($route = $collection->get($route_name)) {
        $route->setOption('_auth', ['basic_auth', 'cookie']);
        $route->setRequirement('_user_is_logged_in', 'TRUE');
      }
    }
  }

}
