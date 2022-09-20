<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

/**
 * Expose a metric for a rolling average of five content release durations.
 *
 * @MetricsCollector(
 *   id = "va_gov_content_release_rolling_average",
 *   title = @Translation("Rolling average of five content release durations"),
 *   description = @Translation("Rolling average of five content release durations")
 * )
 */
class ContentReleaseDurationRollingAverage extends BaseContentReleaseMetricsCollector {

  // This is calculated in another metrics collector, but aliasing it here for
  // visibility.
  public const CONTENT_RELEASE_DURATION_STATE_KEY = ContentReleaseDuration::CONTENT_RELEASE_DURATION_STATE_KEY;

  public const LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY = 'va_gov_build_trigger.metrics.last_updated_content_release_duration';
  public const ROLLING_AVERAGE_STATE_KEY = 'va_gov_build_trigger.metrics.duration_rolling_average';

  /**
   * {@inheritDoc}
   */
  protected function calculate() : int {
    $current_duration = $this->state->get(self::CONTENT_RELEASE_DURATION_STATE_KEY, 0);

    $last_duration = $this->state->get(self::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY);
    if (is_null($last_duration)) {
      $this->state->set(self::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY, $current_duration);
      $last_duration = $current_duration;
    }

    $rolling_average = $this->state->get(self::ROLLING_AVERAGE_STATE_KEY);
    if (is_null($rolling_average)) {
      $this->state->set(self::ROLLING_AVERAGE_STATE_KEY, $last_duration);
      $rolling_average = $last_duration;
    }

    if ($current_duration !== $last_duration) {
      $this->state->set(self::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY, $current_duration);
      $rolling_average = floor((($rolling_average * 4) + $current_duration) / 5);
      $this->state->set(self::ROLLING_AVERAGE_STATE_KEY, $rolling_average);
    }

    return $rolling_average;
  }

}
