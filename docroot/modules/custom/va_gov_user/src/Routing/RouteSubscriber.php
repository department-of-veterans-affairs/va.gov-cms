<?php

namespace Drupal\va_gov_user\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\va_gov_user\Form\VaGovUserMigrateSourceUiForm;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('migrate_source_ui.form')) {
      $route->setDefault('_form', VaGovUserMigrateSourceUiForm::class);
    }
  }

}
