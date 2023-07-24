<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginBase;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * FALSE verbose strategy.
 *
 * This always returns FALSE, but checks the node and logs whether the update
 * would have merited a content release.
 *
 * @EntityEventStrategy(
 *   id = "verbose_false",
 *   label = @Translation("FALSE but Verbose")
 * )
 */
class VerboseFalse extends StrategyPluginBase {

  /**
   * Calculate variables for a specified node.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   *
   * @return array
   *   The variables.
   */
  protected function calculateVariables(VaNodeInterface $node) : array {
    return [
      '%link_to_node' => $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString(),
      '%nid' => $node->id(),
      '%type' => $node->getType(),
    ];
  }

  /**
   * Log whether a content release would have been triggered by this change.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   */
  public function logContentReleaseTriggerDecision(VaNodeInterface $node) {
    $wouldHaveBeenTriggered = $node->shouldTriggerContentRelease();
    $variables['@would'] = $wouldHaveBeenTriggered ? 'would have' : 'would not have';
    $this->logger->info('A content release @would been triggered by a change to %type: %link_to_node (node%nid).', $variables);
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool {
    $this->logContentReleaseTriggerDecision($node);
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getReasonMessage(VaNodeInterface $node) : string {
    return $this->t('This should never be reached because the strategy always indicates that no content release should be triggered.');
  }

}
