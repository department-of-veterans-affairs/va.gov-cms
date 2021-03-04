<?php

namespace Drupal\va_gov_menu_access\AccessChecks;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Determines access rules for particular routes.
 */
class RouteAccessChecks implements AccessInterface {

  /**
   * A custom access check.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   Determine the page route.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The routing interface.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    // Restrict admin menu access.
    $current_user_roles = $account->getRoles();
    $admin_roles = [
      'administrator',
      'content_admin',
    ];
    $admin_role_count = count(array_intersect($admin_roles, $current_user_roles));
    if ($admin_role_count > 0) {
      return AccessResult::allowed();
    }

    if (!$account->hasPermission('VA.gov custom menu access administration')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
