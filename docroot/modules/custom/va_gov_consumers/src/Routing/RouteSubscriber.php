<?php

namespace Drupal\va_gov_consumers\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * OpenAPI json endpoint route alter to allow basic auth.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // We want to allow basic auth on Open Api endpoint.
    $route_provider = \Drupal::service('router.route_provider');
    // This it the path /openapi/jsonapi?_format=json to allow basic auth.
    $graphql_routes = $route_provider->getRoutesByPattern('openapi/jsonapi');
    $graphql_iterator = $graphql_routes->getIterator();

    foreach ($graphql_iterator as $route_name => $route_params) {
      if (($route_name === 'openapi.download') && $route = $collection->get($route_name)) {
        $route->setOption('_auth', ['basic_auth', 'cookie']);
        $route->setRequirement('_user_is_logged_in', 'TRUE');
      }
    }
  }

}
