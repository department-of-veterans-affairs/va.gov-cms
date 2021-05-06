<?php

namespace Drupal\va_gov_menu_access\AccessChecks;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\Routing\Route;

/**
 * Determines access rules for particular routes.
 */
class RouteAccessChecks implements AccessInterface {


  /**
   * The UserPermsService for the current user.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $permsService;

  /**
   * RouteAccessChecks constructor.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $perms_service
   *   The UserPermsService.
   */
  public function __construct(UserPermsService $perms_service) {
    $this->permsService = $perms_service;
  }

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
    if ($this->permsService->hasAdminRole()) {
      return AccessResult::allowed();
    }

    if (!$account->hasPermission('VA.gov custom menu access administration')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
