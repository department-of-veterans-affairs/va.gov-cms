<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Drupal\va_gov_backend\Deploy\LoadMaintenanceFileTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * MaintenanceMode Plugin to return static html and 503.
 *
 * Catch all plugin.
 */
class MaintenanceMode implements DeployPluginInterface {
  use LoadMaintenanceFileTrait;

  /**
   * {@inheritDoc}
   */
  public function match(Request $request): bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function run(Request $request, string $app_root, string $site_path) {
    $html = $this->loadMaintenanceHtml($app_root, $site_path);
    // @TODO make time configurable.
    throw new ServiceUnavailableHttpException(120, $html);
  }

}
