<?php

namespace Drupal\va_gov_backend\Commands;

use Drupal\va_gov_backend\Service\Metrics;
use Drush\Commands\DrushCommands;

/**
 * A Drush interface to the datadog metrics service.
 */
class MetricsCommands extends DrushCommands {

  /**
   * The metrics service.
   *
   * @var \Drupal\va_gov_backend\Service\Metrics
   */
  protected $metrics;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_backend\Service\Metrics $metrics
   *   The metrics service.
   */
  public function __construct(Metrics $metrics) {
    $this->metrics = $metrics;
  }

  /**
   * Send metrics to datadog.
   *
   * @command va-gov:metrics:send
   * @aliases va-gov-metrics-send
   */
  public function sendMetrics() {
    $this->metrics->updateDatadog();
  }

}
