<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Symfony\Component\HttpFoundation\Request;

/**
 * An interface for Deploy plugins.
 *
 * The code is run in settings.php before database, cache or services access.
 */
interface DeployPluginInterface {

  /**
   * Matach on a request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return bool
   *   Should this plugin run?
   */
  public function match(Request $request) : bool;

  /**
   * Run the plugin and throw the exception.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $app_root
   *   The Drupal app root.
   * @param string $site_path
   *   The Drupal site path.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
   */
  public function run(Request $request, string $app_root, string $site_path) : ?bool;

}
