<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

/**
 * Expose a metric for a rolling average of five content release intervals.
 *
 * @MetricsCollector(
 *   id = "va_gov_content_release_interval_rolling_average",
 *   title = @Translation("Rolling average of five content release intervals"),
 *   description = @Translation("Rolling average of five content release intervals")
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

    $last_interval = $this->state->get(self::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY);
    if (is_null($last_interval)) {
      $this->state->set(self::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY, $current_interval);
      $last_interval = $current_interval;
    }

    $rolling_average = $this->state->get(self::ROLLING_AVERAGE_STATE_KEY);
    if (is_null($rolling_average)) {
      $this->state->set(self::ROLLING_AVERAGE_STATE_KEY, $last_interval);
      $rolling_average = $last_interval;
    }

    if ($current_interval !== $last_interval) {
      $this->state->set(self::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY, $current_interval);
      $rolling_average = floor((($rolling_average * 4) + $current_interval) / 5);
      $this->state->set(self::ROLLING_AVERAGE_STATE_KEY, $rolling_average);
    }

    return $rolling_average;
  }

}
