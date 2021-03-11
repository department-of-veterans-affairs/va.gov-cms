<?php

namespace Drupal\va_gov_build_trigger\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   EnvironmentDiscovery service.
   */
  public function __construct(EnvironmentDiscovery $environmentDiscovery) {
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('va_gov_build_trigger.build_trigger_form')) {
      if ($buildTriggerFormClass = $this->environmentDiscovery->getBuildTriggerFormClass()) {
        $route->setDefault('_form', $buildTriggerFormClass);
      }
    }
  }

}
