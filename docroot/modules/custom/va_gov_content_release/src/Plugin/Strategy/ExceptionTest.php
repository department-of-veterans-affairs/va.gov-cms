<?php

namespace Drupal\va_gov_content_release\Plugin\Strategy;

use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginBase;

/**
 * Exception test strategy.
 *
 * This always throws an exception.
 *
 * @ContentReleaseStrategy(
 *   id = "test_exception",
 *   label = @Translation("Exception Test")
 * )
 */
class ExceptionTest extends StrategyPluginBase {

  /**
   * {@inheritDoc}
   */
  public function triggerContentRelease() : void {
    throw new StrategyErrorException('This is a test exception.');
  }

}
