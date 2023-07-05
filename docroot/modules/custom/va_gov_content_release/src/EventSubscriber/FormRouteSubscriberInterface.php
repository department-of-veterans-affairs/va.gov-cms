<?php

namespace Drupal\va_gov_content_release\EventSubscriber;

use Symfony\Component\Routing\Route;

/**
 * Sets the form class for the content release form based on the form resolver.
 *
 * This normally will be set per-environment, but we can override it for testing
 * purposes.
 */
interface FormRouteSubscriberInterface {

  /**
   * Alter the specified route accordingly.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route.
   */
  public function alterRoute(Route $route);

}
