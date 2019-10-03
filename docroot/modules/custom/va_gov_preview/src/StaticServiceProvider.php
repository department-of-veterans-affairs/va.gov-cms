<?php

namespace Drupal\va_gov_preview;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds application/static+html as known format.
 */
class StaticServiceProvider implements ServiceModifierInterface {

  /**
   * The path the WEB static files are rendered into, relative to DRUPAL_ROOT.
   *
   * @var String
   */
  const STATIC_DIRECTORY_NAME = 'static';

  /**
   * The filename to look for inside Metalsmith-generated folders.
   *
   * @var String
   */
  const INDEX_FILE_NAME = 'index.html';

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') && is_a($container->getDefinition('http_middleware.negotiation')->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
      $container->getDefinition('http_middleware.negotiation')->addMethodCall('registerFormat', ['html', ['application/static+html']]);
    }
  }

  /**
   * Convert URL path to the static file path on the server.
   *
   * @param string $url_path
   *   Path requested by the browser.
   */
  public static function urlPathToServerPath($url_path) {
    return implode(DIRECTORY_SEPARATOR, [
      DRUPAL_ROOT,
      self::STATIC_DIRECTORY_NAME,
      $url_path,
      self::INDEX_FILE_NAME,
    ]);

  }

}
