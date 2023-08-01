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

  /**
   * {@inheritDoc}
   */
  public function getReasonMessage(VaNodeInterface $node) : string {
    $variables = [
      '%link_to_node' => $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString(),
      '%nid' => $node->id(),
      '%type' => $node->getType(),
    ];
    return $this->t('A content release was triggered by a change to %type: %link_to_node (node%nid).', $variables);
  }

}
