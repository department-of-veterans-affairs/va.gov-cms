<?php

namespace Drupal\va_gov_preview;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds application/static+html as known format.
 */
class StaticServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') && is_a($container->getDefinition('http_middleware.negotiation')->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
      $container->getDefinition('http_middleware.negotiation')->addMethodCall('registerFormat', ['html', ['application/static+html']]);
    }
  }

}
