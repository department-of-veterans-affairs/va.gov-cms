<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginBase;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * TRUE test strategy.
 *
 * This always returns TRUE.
 *
 * @EntityEventStrategy(
 *   id = "test_true",
 *   label = @Translation("TRUE Test")
 * )
 */
class TrueTest extends StrategyPluginBase {

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool {
    return TRUE;
  }

}
