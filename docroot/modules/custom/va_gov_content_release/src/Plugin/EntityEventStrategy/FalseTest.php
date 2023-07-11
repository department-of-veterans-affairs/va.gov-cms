<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginBase;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * FALSE test strategy.
 *
 * This always returns FALSE.
 *
 * @EntityEventStrategy(
 *   id = "test_false",
 *   label = @Translation("FALSE Test")
 * )
 */
class FalseTest extends StrategyPluginBase {

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool {
    return FALSE;
  }

}
