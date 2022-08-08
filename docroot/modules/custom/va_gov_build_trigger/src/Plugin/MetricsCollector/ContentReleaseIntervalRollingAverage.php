<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;

/**
 * Expose a metric for a rolling average of five content release durations.
 *
 * @MetricsCollector(
 *   id = "va_gov_content_release_rolling_average",
 *   title = @Translation("Rolling average of five content release durations"),
 *   description = @Translation("Rolling average of five content release durations")
 * )
 */
class ContentReleaseIntervalRollingAverage extends BaseContentReleaseMetricsCollector {

  // This is calculated elsewhere, but aliasing it here for visibility.
  public const CONTENT_RELEASE_INTERVAL_STATE_KEY = ContentReleaseInterval::CONTENT_RELEASE_INTERVAL_STATE_KEY;
  public const LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY = 'va_gov_build_trigger.metrics.last_updated_content_release_interval';
  public const ROLLING_AVERAGE_STATE_KEY = 'va_gov_build_trigger.metrics.content_release_interval_rolling_average';

  /**
   * {@inheritDoc}
   */
  protected function calculate() : int {
    $current_interval = $this->state->get(self::CONTENT_RELEASE_INTERVAL_STATE_KEY, 0);
    $last_interval = $this->state->get(self::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY, $current_interval);
    $rolling_average = $this->state->get(self::ROLLING_AVERAGE_STATE_KEY, $current_interval);

    if ($current_interval !== $last_interval) {
      $this->state->set(self::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY, $current_interval);
      $rolling_average = floor((($rolling_average * 4) + $current_interval) / 5);
      $this->state->set(self::ROLLING_AVERAGE_STATE_KEY, $rolling_average);
    }

    return $rolling_average;
  }

}
