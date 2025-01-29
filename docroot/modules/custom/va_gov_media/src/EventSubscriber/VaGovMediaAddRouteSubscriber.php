<?php

declare(strict_types = 1);

namespace Drupal\va_gov_media\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Route subscriber.
 */
final class VaGovMediaAddRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
//    $addFormRoute = $collection->get('entity.media.add_form');
//    $addFormRoute?->setRequirements([
//      '_role' => 'administrator+content_admin',
//    ]);
//    $addPageRoute = $collection->get('entity.media.add_page');
//    $addPageRoute?->setRequirements([
//      '_role' => 'administrator+content_admin',
//    ]);
  }

}
