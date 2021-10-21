<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Drupal\va_gov_backend\Deploy\SuccessHTTPException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Health check plugin for health checks.
 */
class HealthCheck implements DeployPluginInterface {

  /**
   * Add additional downtime user-agent.
   *
   * Two different downtime user agents are required since we want uptime graphs
   * to work correctly in both Grafana and Datadog. The addition of a Datadog
   * deploy user-agent is to quell alerts during deploys.
   * Prometheus no longer alerts.
   */
  public const VAGOV_DOWNTIME_DETECT_USER_AGENT_PROMETHEUS = 'curl-prometheus-check';
  public const VAGOV_DOWNTIME_DETECT_USER_AGENT_DATADOG = 'Datadog/Synthetics';

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
    if ($user_agent === static::VAGOV_DOWNTIME_DETECT_USER_AGENT_PROMETHEUS || $user_agent === static::VAGOV_DOWNTIME_DETECT_USER_AGENT_DATADOG) {
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
