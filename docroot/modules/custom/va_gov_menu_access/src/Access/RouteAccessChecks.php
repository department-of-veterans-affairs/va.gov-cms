<?php

namespace Drupal\va_gov_menu_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Determines access rules for particular routes.
 */
class RouteAccessChecks implements AccessInterface, ContainerInjectionInterface {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // This create exists due to a weird thing, where DI of the argument from
    // services.yml is not injecting the argument of the user_perms service.
    // As a result it has to be done here in the create().
    return new static(
      $container->get('va_gov_user.user_perms')
    );

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
    if ($this->permsService->hasAdminRole()
    || $account->hasPermission('administer cms custom menu access')) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
