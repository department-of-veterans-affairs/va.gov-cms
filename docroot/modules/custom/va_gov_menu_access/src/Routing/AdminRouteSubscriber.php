<?php

namespace Drupal\va_gov_menu_access\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Limits access to admin menu paths to prevent non admin users from editing.
 */
class AdminRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $menu_routes = [
      'entity.menu.collection',
      'entity.menu.edit_form',
      'entity.menu.add_form',
    ];
    foreach ($collection->all() as $routename => $route) {
      if (in_array($routename, $menu_routes)) {
        $route->setRequirement(
          '_custom_access',
          '\Drupal\va_gov_menu_access\Access\RouteAccessChecks::access'
          );
      }
    }
  }

}
