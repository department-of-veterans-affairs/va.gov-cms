<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;

/**
 * Builds and sends metrics to Datadog.
 */
class Datadog {
  const METRICS_INGEST_URL = "https://api.ddog-gov.com/api/v1/series";

  /**
   * A Guzzle HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $http;

  /**
   * The datadog API key.
   *
   * @var string
   */
  protected $apikey;

  /**
   * Metrics constructor.
   *
   * @param \GuzzleHttp\Client $http
   *   A Guzzle HTTP client.
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings manager.
   */
  public function __construct(Client $http, Settings $settings) {
    $this->http = $http;
    $this->apikey = $settings->get('cms_datadog_api_key');
  }

  /**
   * Send metrics to Datadog.
   *
   * @param \PNX\Prometheus\Metric[] $metrics
   *   The metrics to be included in the metrics object.
   * @param string $environment
   *   The name of the environment that the metrics belong to (one of local,
   *   dev, staging, prod).
   */
  public function send(array $metrics, $environment) {
    $this->sendMetricsToDatadog($this->buildMetricsObject($metrics, $environment));
  }

  /**
   * Get a metrics prefix from the provided environment name.
   *
   * @param string $environment
   *   The name of the environment that the metrics belong to (one of local,
   *   dev, staging, prod).
   *
   * @return string
   *   The prefix to apply to the metrics name.
   */
  protected function getMetricPrefix($environment): string {
    return 'dsva_vagov.cms.' . $environment . '.';
  }

  /**
   * Given a list of collected metrics, build a metrics object for datadog.
   *
   * @param \PNX\Prometheus\Metric[] $metricsList
   *   The metrics to be included in the metrics object.
   * @param string $environment
   *   The name of the environment that the metrics belong to (one of local,
   *   dev, staging, prod).
   *
   * @return object
   *   An object following the Datadog API spec for the metrics API.
   */
  protected function buildMetricsObject(array $metricsList, string $environment): object {
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
        $m->metric = $this->getMetricPrefix($environment) . $labeled_value->getName();

        $labels = $labeled_value->getLabels();

        if (!empty($labels)) {
          // If we have any labels on the current value, they should be sent as
          // tags. This is not a perfect mapping between prometheus and datadog,
          // but it will work well enough.
          foreach ($labeled_value->getLabels() as $k => $v) {
            // Phpstan thinks that either $k or $v is an array. They are not.
            // LabelledValue ensures that they are both strings that match a
            // particular regex.
            // @phpstan-ignore-next-line
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
        $m->tags[] = "env:" . $environment;

        $m->points = [
          [
            $timestamp,
            $labeled_value->getValue(),
          ],
        ];

        $metrics->series[] = $m;
      }

    }

    return $metrics;
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
        'DD-API-KEY' => $this->apikey,
      ],
      'body' => json_encode($metrics),
    ]);
  }

}
