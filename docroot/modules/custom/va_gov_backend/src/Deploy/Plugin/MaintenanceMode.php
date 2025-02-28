<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Drupal\va_gov_backend\Deploy\LoadMaintenanceFileTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    $expected_update_duration = '10';
    $html = preg_replace('/\{\{\s*update_duration\s*\}\}/', $expected_update_duration, $html);
    $headers = [
      'Content-Type' => 'text/html',
      'Retry-After' => 120,
    ];
    $response = new Response($html, 503, $headers);
    $response->send();
    // We need to throw an exception to stop the request.
    throw new ServiceUnavailableHttpException();
  }

}
