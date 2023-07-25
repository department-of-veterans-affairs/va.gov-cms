<?php

namespace Drupal\va_gov_content_release\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\va_gov_content_release\Form\Resolver\ResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the form class for the content release form based on the form resolver.
 *
 * This normally will be set per-environment, but we can override it for testing
 * purposes.
 */
class FormRouteSubscriber extends RouteSubscriberBase implements FormRouteSubscriberInterface {

  const ROUTE_NAME = 'va_gov_content_release.form';

  /**
   * Form Resolver service.
   *
   * @var \Drupal\va_gov_content_release\Form\ResolverInterface
   */
  protected $formResolver;

  /**
   * The constructor.
   *
   * @param \Drupal\va_gov_content_release\Form\ResolverInterface $formResolver
   *   Form resolver service.
   */
  public function __construct(ResolverInterface $formResolver) {
    $this->formResolver = $formResolver;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get(static::ROUTE_NAME)) {
      $this->alterRoute($route);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoute(Route $route) {
    $route->setDefault('_form', $this->formResolver->getFormClass());
  }

}
