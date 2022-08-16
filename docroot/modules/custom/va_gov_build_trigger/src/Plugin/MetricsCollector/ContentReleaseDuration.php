<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;

/**
 * Expose a metric for the last content release duration.
 *
 * @MetricsCollector(
 *   id = "va_gov_last_content_release_duration",
 *   title = @Translation("Last content release duration"),
 *   description = @Translation("Last content release duration")
 * )
 */
class ContentReleaseDuration extends BaseContentReleaseMetricsCollector {

  public const CONTENT_RELEASE_DURATION_STATE_KEY = 'va_gov_build_trigger.metrics.content_release_duration';

  /**
   * {@inheritDoc}
   */
  protected function calculate() : int {
    $last_dispatch_time = $this->state->get(ReleaseStateManager::LAST_RELEASE_DISPATCH_KEY);
    $last_complete_time = $this->state->get(ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY);
    $last_error_time = $this->state->get(ReleaseStateManager::LAST_RELEASE_ERROR_KEY);

    $release_in_progress = ($last_dispatch_time > $last_complete_time);
    $last_release_errored = ($last_dispatch_time < $last_error_time);

    // If we can calulate a new metric, do it.
    if (!$release_in_progress && !$last_release_errored) {
      $duration = $last_complete_time - $last_dispatch_time;
      $this->state->set(self::CONTENT_RELEASE_DURATION_STATE_KEY, $duration);
      return $duration;
    }

    // Otherwise, return the last metric we calculated.
    return $this->state->get(self::CONTENT_RELEASE_DURATION_STATE_KEY, 0);
  }

}
