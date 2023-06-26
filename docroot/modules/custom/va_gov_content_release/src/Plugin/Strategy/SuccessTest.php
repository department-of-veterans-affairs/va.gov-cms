<?php

namespace Drupal\va_gov_content_release\Plugin\Strategy;

use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginBase;

/**
 * Success test strategy.
 *
 * This always succeeds.
 *
 * @ContentReleaseStrategy(
 *   id = "test_success",
 *   label = @Translation("Success Test")
 * )
 */
class SuccessTest extends StrategyPluginBase {

  /**
   * {@inheritDoc}
   */
  public function triggerContentRelease() : void {}

}
