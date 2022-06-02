<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\va_gov_build_trigger\Controller\ContentReleaseNotificationController;
use Drupal\va_gov_build_trigger\Controller\ContentReleaseRequestController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Define routes for content release notifications.
 *
 * I did it this way so that there weren't a zillion repeated blocks in the
 * routing.yml for the notification routes.
 */
class RouteProvider {

  /**
   * Builds the routes for each state that the content release can notify about.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   The notification routes.
   */
  public function notificationRoutes() {
    $notification_states = ContentReleaseNotificationController::allowedNotifications();

    $routes = new RouteCollection();

    foreach ($notification_states as $state) {
      $route = new Route(
        '/api/content_release/' . $state,
        // Defaults.
        [
          '_controller' => ContentReleaseNotificationController::class . '::' . $state,
        ],
        // Requirements.
        [
          '_permission' => 'access bulletin queue trigger api',
          '_user_is_logged_in' => 'TRUE',
        ],
        // Options.
        [
          'no_cache' => TRUE,
          '_auth' => ['basic_auth', 'cookie'],
        ]
      );

      $routes->add('va_gov_build_trigger.content_release_notification.' . $state, $route);
    }

    return $routes;
  }

  /**
   * Builds routes for external services to request a content release.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   The content release request routes.
   */
  public function buildRequestRoutes() {
    $routes = new RouteCollection();

    $route = new Route(
      '/api/content_release/request_build',
      // Defaults.
      [
        '_controller' => ContentReleaseRequestController::class . '::requestBuild',
      ],
      // Requirements.
      [
        '_permission' => 'handle content release notifications',
      ],
      // Options.
      [
        'no_cache' => TRUE,
      ],
    );

    $routes->add('va_gov_build_trigger.content_release_request_build', $route);

    return $routes;
  }

}
