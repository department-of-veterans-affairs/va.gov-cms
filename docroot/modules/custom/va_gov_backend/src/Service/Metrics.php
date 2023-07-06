<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Site\Settings;
use Drupal\prometheus_exporter\MetricsCollectorManager;

/**
 * Sends prometheus metrics to Datadog.
 */
class Metrics {
  /**
   * The metrics collector manager.
   *
   * @var \Drupal\prometheus_exporter\MetricsCollectorManager
   */
  protected $metricsCollectorManager;

  /**
   * Drupal site settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Datadog metrics handler.
   *
   * @var \Drupal\va_gov_backend\Service\Datadog
   */
  protected $datadog;

  /**
   * Metrics constructor.
   *
   * @param \Drupal\prometheus_exporter\MetricsCollectorManager $metricsCollectorManager
   *   The metrics collector manager.
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings manager.
   * @param \Drupal\va_gov_backend\Service\Datadog $datadog
   *   The datadog metrics handler.
   */
  public function __construct(MetricsCollectorManager $metricsCollectorManager, Settings $settings, Datadog $datadog) {
    $this->metricsCollectorManager = $metricsCollectorManager;
    $this->settings = $settings;
    $this->datadog = $datadog;
  }

  /**
   * Collect, format, and send metrics to Datadog.
   */
  public function updateDatadog(): void {
    if (!$this->shouldSendMetrics()) {
      return;
    }

    $metrics = $this->metricsCollectorManager->collectMetrics();
    $this->datadog->send($metrics, $this->getEnvironment());
  }

  /**
   * Don't send metrics to datadog if we're not on a monitored environment.
   *
   * Since metrics will occasionally need to be worked on locally or on other
   * environments, this method also allows developers to override the behavior
   * in settings.php.
   *
   * @return bool
   *   Whether or not metrics should be sent to datadog.
   */
  protected function shouldSendMetrics(): bool {
    $env_is_brd = $this->settings->get('va_gov_frontend_build_type') == "brd";
    $override_present = $this->settings->get('va_gov_force_sending_metrics', FALSE);

    return $env_is_brd || $override_present;
  }

  /**
   * Return the name of the environment.
   */
  protected function getEnvironment(): string {
    // @todo This should probably use a different setting.
    switch ($this->settings->get('va_gov_frontend_build_type')) {
      case "brd":
        return $this->settings->get('github_actions_deploy_env', 'unknown');

      case "local":
        return "local";

      default:
        return "unknown";
    }
  }

}
