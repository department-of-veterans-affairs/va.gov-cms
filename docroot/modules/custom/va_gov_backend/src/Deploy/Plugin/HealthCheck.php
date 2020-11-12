<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Drupal\va_gov_backend\Deploy\SuccessHTTPException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Health check plugin for health checks.
 */
class HealthCheck implements DeployPluginInterface {

  public const VAGOV_DOWNTIME_DETECT_USER_AGENT = 'curl-prometheus-check';

  /**
   * {@inheritDoc}
   */
  public function match(Request $request): bool {
    $current_path = $request->getPathInfo();
    if ($current_path === '/health') {
      return TRUE;
    }

    // The system which detects and measures downtime shouldn't
    // register a downtime during deploys.
    $user_agent = $request->headers->get('User-Agent');
    if ($user_agent === static::VAGOV_DOWNTIME_DETECT_USER_AGENT) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function run(Request $request, string $app_root, string $site_path) {
    throw new SuccessHTTPException('Everything is awesome');
  }

}
