<?php

namespace Drupal\va_gov_events\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('smart_date_recur.instance.reschedule')) {
      $route->setDefault('_form', '\Drupal\va_gov_events\Form\SmartDateOverrideForm');
    }
    if ($route = $collection->get('smart_date_recur.instance.remove')) {
      $route->setDefault('_form', '\Drupal\va_gov_events\Form\SmartDateRemoveInstanceForm');
    }
    if ($route = $collection->get('smart_date_recur.instances')) {
      $route->setDefault('_controller', '\Drupal\va_gov_events\Controller\Instances::listInstances');
    }
    if ($route = $collection->get('smart_date_recur.apply_changes')) {
      $route->setDefault('_controller', '\Drupal\va_gov_events\Controller\Instances::applyChanges');
    }
    if ($route = $collection->get('smart_date_recur.instance.reschedule.ajax')) {
      $route->setDefault('_controller', '\Drupal\va_gov_events\Controller\Instances::reschedule');
    }
    if ($route = $collection->get('smart_date_recur.instance.remove.ajax')) {
      $route->setDefault('_controller', '\Drupal\va_gov_events\Controller\Instances::removeAjax');
    }
    if ($route = $collection->get('smart_date_recur.instance.revert.ajax')) {
      $route->setDefault('_controller', '\Drupal\va_gov_events\Controller\Instances::revertAjax');
    }
  }

}
