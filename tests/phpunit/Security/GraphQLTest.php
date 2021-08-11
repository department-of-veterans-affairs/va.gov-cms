<?php

namespace tests\phpunit\Security;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to check access to graphql data.
 */
class GraphQLTest extends ExistingSiteBase {

  /**
   * A test method to check access to GraphQL service.
   *
   * @group security
   * @group all
   */
  public function testGraphqlAccess() {
    $router = $this->container->get('router');
    $collection = $router->getRouteCollection();
    $route_provider = $this->container->get('router.route_provider');
    $graphql_routes = $route_provider->getRoutesByPattern('graphql');
    $graphql_iterator = $graphql_routes->getIterator();
    $this->assertGreaterThan(0, $graphql_iterator->count());
    foreach ($graphql_iterator as $route_name => $route_params) {
      $route = $collection->get($route_name);
      $this->assertEquals(['basic_auth', 'cookie'], $route->getOption('_auth'));
      $this->assertEquals('TRUE', $route->getRequirement('_user_is_logged_in'));
    }
  }

}
