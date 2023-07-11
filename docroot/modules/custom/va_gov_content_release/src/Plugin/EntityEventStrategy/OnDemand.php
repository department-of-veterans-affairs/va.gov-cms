<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginBase;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * On-Demand strategy.
 *
 * This returns TRUE based on the nature of the changes to the content in
 * question.
 *
 * @EntityEventStrategy(
 *   id = "on_demand",
 *   label = @Translation("On-Demand")
 * )
 */
class OnDemand extends StrategyPluginBase {

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool {
    return $node->shouldTriggerContentRelease();
  }

}
