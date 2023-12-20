<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginBase;
use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * Exception test strategy.
 *
 * This always throws an exception.
 *
 * @EntityEventStrategy(
 *   id = "test_exception",
 *   label = @Translation("Exception Test")
 * )
 */
class ExceptionTest extends StrategyPluginBase {

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool {
    throw new StrategyErrorException('This is a test exception.');
  }

  /**
   * {@inheritDoc}
   */
  public function getReasonMessage(VaNodeInterface $node) : string {
    return $this->t('This should never be reached because shouldTriggerContentRelease() throws an exception.');
  }

}
