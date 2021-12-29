<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Site\Settings;
use Drupal\prometheus_exporter\MetricsCollectorManager;
use GuzzleHttp\Client;

/**
 * Sends prometheus metrics to Datadog.
 */
class Metrics {

  const METRICS_INGEST_URL = "https://api.datadoghq.com/api/v1/series";

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
   * A Guzzle HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $http;

  /**
   * Metrics constructor.
   *
   * @param \Drupal\prometheus_exporter\MetricsCollectorManager $metricsCollectorManager
   *   The metrics collector manager.
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings manager.
   * @param \GuzzleHttp\Client $http
   *   A Guzzle HTTP client.
   */
  public function __construct(MetricsCollectorManager $metricsCollectorManager, Settings $settings, Client $http) {
    $this->metricsCollectorManager = $metricsCollectorManager;
    $this->settings = $settings;
    $this->http = $http;
  }

  /**
   * Collect, format, and send metrics to Datadog.
   */
  public function updateDatadog(): void {
    if (!$this->shouldSendMetrics()) {
      return;
    }

    $metrics = $this->metricsCollectorManager->collectMetrics();
    $formatted_metrics = $this->buildMetricsObject($metrics);
    $this->sendMetricsToDatadog($formatted_metrics);
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
  protected function shouldSendMetrics() : bool {
    $env_is_brd = $this->settings->get('va_gov_frontend_build_type') == "brdgha";
    $override_present = $this->settings->get('va_gov_force_sending_metrics', FALSE);

    return $env_is_brd || $override_present;
  }

  /**
   * Build a prefix for the metrics that are pushed to Datadog.
   */
  protected function getMetricPrefix() : string {
    // @todo This should probably use a different setting.
    switch ($this->settings->get('va_gov_frontend_build_type')) {
      case "brdgha":
        return "dsva_vagov.cms." . $this->settings->get('github_actions_deploy_env') . ".";

      case "lando":
        return "dsva_vagov.cms.local.";

      default:
        return FALSE;
    }
  }

  /**
   * Given a list of collected metrics, build a metrics object for datadog.
   *
   * @param \PNX\Prometheus\Metric[] $metricsList
   *   The metrics to be included in the metrics object.
   *
   * @return object
   *   An object following the Datadog API spec for the metrics API.
   */
  protected function buildMetricsObject(array $metricsList): object {
    $timestamp = time();

    $metrics = new \stdClass();
    $metrics->series = [];

    foreach ($metricsList as $metric) {
      /** @var \PNX\Prometheus\Metric $metric */

      // @todo Figure out how to reliably send non-gauge metrics to Datadog.
      // Right now, we only expose gaguge metrics to Prometheus, so this is not
      // a limitation for our specific needs.
      if ($metric->getType() !== "gauge") {
        continue;
      }

      foreach ($metric->getLabelledValues() as $labeled_value) {
        /** @var \PNX\Prometheus\LabelledValue $labeled_value */
        $m = new \stdClass();
        $m->type = "gauge";
        $m->timestamp = $timestamp;
        $m->tags = [];

        $labels = $labeled_value->getLabels();

        if (!empty($labels)) {
          // If we have any labels on the current value, they should be sent as
          // tags. This is not a perfect mapping between prometheus and datadog,
          // but it will work well enough.
          foreach ($labeled_value->getLabels() as $k => $v) {
            $m->tags[] = $k . ":" . $v;
          }
        }
        else {
          // This is important because all of the metrics for e.g. node count
          // will be sent as dsva_vagov.cms.prod.drupal_node_count_total. If
          // we want to query the unsegmented total (not broken down by bundle
          // or publishing status), we still have to have some label to use in
          // the query in Datadog because there is no way to query for a metric
          // that does *not* have a particular tag.
          $m->tags[] = "unsegmented";
        }

        // Since we'll be getting these metrics from every environment, ensure
        // that we can segment the metrics by environment.
        $m->tags[] = "env:" . $this->getMetricPrefix();

        $m->points = [
          $timestamp,
          $labeled_value->getValue(),
        ];

        $metrics->series[] = $m;
      }

      return $metrics;
    }
  }

  /**
   * Send metrics to datadog.
   *
   * @param object $metrics
   *   An object following the Datadog API spec for the metrics API.
   */
  protected function sendMetricsToDatadog(object $metrics): void {
    // @todo this may need some kind of failure detection, but it has not been
    // implemented here because we're running this every 15 minutes, so chances
    // are that metrics will be updated in subsequent runs. It isn't critical
    // to have these metrics updated in real-time or anywhere close to it, so a
    // delay of up to a day between successful deliveries is acceptable.
    $this->http->post(self::METRICS_INGEST_URL, [
      'headers' => [
        'Content-Type' => 'text/json',
        'DD-API-KEY' => $this->settings->get('cms_datadog_api_key'),
      ],
      'body' => json_encode($metrics),
    ]);
  }

}
