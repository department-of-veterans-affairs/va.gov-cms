<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

/**
 * Expose a metric for the interval between content releases.
 *
 * @MetricsCollector(
 *   id = "va_gov_content_release_attempts_since_last_success",
 *   title = @Translation("The number of attempts since the last successful content release"),
 *   description = @Translation("The number of attempts since the last successful content release")
 * )
 */
class ContentReleaseAttemptsSinceLastSuccess extends BaseContentReleaseMetricsCollector {

  public const CONTENT_RELEASE_ATTEMPTS_STATE_KEY = 'va_gov_build_trigger.metrics.content_release_attempts';

  /**
   * {@inheritDoc}
   */
  protected function calculate() : int {
    // This is calculated in ContentReleaseErrorSubscriber.
    return $this->state->get(self::CONTENT_RELEASE_ATTEMPTS_STATE_KEY, 0);
  }

}
