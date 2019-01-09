<?php

namespace Drupal\va_gov_graphql\Routing;

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

    if ($route = $collection->get('graphql.query')) {
      $route->setOptions(['_auth', 'basic_auth']);
      $route->setRequirements(['_user_is_logged_in', 'TRUE']);
    }
  }

}
