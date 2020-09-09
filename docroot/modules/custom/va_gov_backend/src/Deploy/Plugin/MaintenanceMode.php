<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class MaintenanceMode implements DeployPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function match(Request $request): bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function run(Request $request, string $app_root, string $site_path): void {
    $html = $this->loadMaintenanceHTML($app_root, $site_path);
    throw new ServiceUnavailableHttpException(120, $html);
  }

  /**
   * Load the static html maintenance file.
   *
   * @return string|null
   *   html to use as the page response.
   */
  protected function loadMaintenanceHTML(string $app_root, string $site_path) : ?string {
    // @TODO make this configurable.
    $file = $app_root . '/' . $site_path . '/maintenance.html';
    // We don't have drupal file schema set up yet so we do this old school.
    if (file_exists($file)) {
      return file_get_contents($file);
    }

    return NULL;
  }
}
