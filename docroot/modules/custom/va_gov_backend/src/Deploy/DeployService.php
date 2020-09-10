<?php

namespace Drupal\va_gov_backend\Deploy;

use Drupal\va_gov_backend\Deploy\Plugin\HealthCheck;
use Drupal\va_gov_backend\Deploy\Plugin\MaintenanceMode;
use Symfony\Component\HttpFoundation\Request;

/**
 * A class to control what happens in a deploy process durring settings.php.
 *
 * This all occurs before the database, cache and Drupal services are available.
 */
class DeployService {

  /**
   * A list of the deploy static class.
   *
   * This are loaded and process in order.  The first match wins.
   *
   * @return \Drupal\va_gov_backend\Deploy\Plugin\DeployPluginInterface[]
   *   Array of plugins class names.
   */
  public static function deployPlugins() : array {
    return [
      HealthCheck::class,
      MaintenanceMode::class,
    ];
  }

  /**
   * Process any plugins.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $app_root
   *   The Drupal app root.
   * @param string $site_path
   *   The Drupal site path.
   */
  public function run(Request $request, string $app_root, string $site_path) : void {
    foreach (static::deployPlugins() as $plugin) {
      /** @var \Drupal\va_gov_backend\Deploy\Plugin\DeployPluginInterface $deploy_plugin */
      $deploy_plugin = new $plugin();
      if ($deploy_plugin->match($request)) {
        $deploy_plugin->run($request, $app_root, $site_path);
      }
    }
  }

}
