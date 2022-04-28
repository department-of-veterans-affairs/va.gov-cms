<?php

namespace Drupal\va_gov_graphql\Routing;

use Drupal\graphql\Routing\ExplorerRoutes as GraphQLExplorerRoutes;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Add a custom route for GraphQL explorer.
 */
class ExplorerRoutes extends GraphQLExplorerRoutes {

  /**
   * {@inheritDoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = new RouteCollection();

    foreach ($this->schemaManager->getDefinitions() as $key => $definition) {
      $routes->add("graphql.explorer.$key", new Route("{$definition['path']}/explorer", [
        'schema' => $key,
        '_controller' => '\Drupal\va_gov_graphql\Controller\ExplorerController::viewExplorer',
        '_title' => 'GraphiQL',
      ], [
        '_permission' => 'use graphql explorer',
      ], [
        '_admin_route' => 'TRUE',
      ]));
    }

    $collection->addCollection($routes);
  }

}
