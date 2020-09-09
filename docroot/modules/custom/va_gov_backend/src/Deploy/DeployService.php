<?php

namespace Drupal\va_gov_backend\Deploy;

use Drupal\va_gov_backend\Deploy\Plugin\DeployPluginInterface;
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
    $deploy_plugin = $this->match($request);
    if (!$deploy_plugin) {
      return;
    }

    $deploy_plugin->run($request, $app_root, $site_path);
  }

  /**
   * Look for a plugin match.  First match wins.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The drupal request object.
   *
   * @return \Drupal\va_gov_backend\Deploy\Plugin\DeployPluginInterface|null
   *   The deploy plugin or NULL if none match.
   */
  protected function match(Request $request) : ?DeployPluginInterface {
    foreach (static::deployPlugins() as $plugin) {
      $deploy_plugin = new $plugin();
      if ($deploy_plugin->match($request)) {
        return $deploy_plugin;
      }
    }

    return NULL;
  }

}
