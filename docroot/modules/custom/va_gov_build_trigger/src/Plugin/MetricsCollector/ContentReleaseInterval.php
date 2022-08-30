<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

/**
 * Expose a metric for the interval between content releases.
 *
 * @MetricsCollector(
 *   id = "va_gov_content_release_interval",
 *   title = @Translation("The amount of time between content releases"),
 *   description = @Translation("The amount of time between content releases")
 * )
 */
class ContentReleaseInterval extends BaseContentReleaseMetricsCollector {

  public const CONTENT_RELEASE_INTERVAL_STATE_KEY = 'va_gov_build_trigger.metrics.content_release_interval';

  /**
   * {@inheritDoc}
   */
  protected function calculate() : int {
    // This is calculated in ContentReleaseIntervalSubscriber.
    return $this->state->get(self::CONTENT_RELEASE_INTERVAL_STATE_KEY, 0);
  }

}
