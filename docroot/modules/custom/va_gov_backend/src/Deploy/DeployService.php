<?php

namespace Drupal\va_gov_backend\Deploy;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_backend\Deploy\Plugin\FeatureFlag;
use Drupal\va_gov_backend\Deploy\Plugin\HealthCheck;
use Drupal\va_gov_backend\Deploy\Plugin\MaintenanceMode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A class to control what happens in a deploy process during settings.php.
 *
 * This all occurs before the database, cache and Drupal services are available.
 */
class DeployService {

  /**
   * Initiate the DeployService class.
   *
   * @param array $settings
   *   The Drupal settings array.
   *
   * @return \Drupal\va_gov_backend\Deploy\DeployService
   *   The deploy service.
   */
  public static function create(array $settings = []) : DeployService {
    // We must instantiate a Settings object here since we are
    // before Drupal core has a chance.
    new Settings($settings);

    return new static();
  }

  /**
   * A list of the deploy static class.
   *
   * This are loaded and process in order.  The first match wins.
   *
   * @return string[]
   *   Array of plugins class names.
   */
  public static function deployPlugins() : array {
    return [
      FeatureFlag::class,
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
        // If we have a Symfony Response Object then send it.
        $response = $deploy_plugin->run($request, $app_root, $site_path);
        if (!empty($response) && ($response instanceof Response)) {
          $response->send();
          // We exit here because otherwise Drupal will try to run it's handler.
          exit();
        }
      }
    }
  }

}
