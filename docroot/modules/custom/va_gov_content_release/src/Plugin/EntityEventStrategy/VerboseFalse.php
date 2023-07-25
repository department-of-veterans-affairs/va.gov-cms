<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginBase;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * Verbose FALSE strategy.
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
   * Construct a text link to a node.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   *
   * @return string
   *   The link.
   */
  public function getNodeLink(VaNodeInterface $node): string {
    try {
      return $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString();
    }
    catch (\Throwable $exception) {
      // No link could be created, likely because we're in a test environment.
      return 'http://invalid-link.example.com/';
    }
  }

  /**
   * Calculate variables for a specified node.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   *
   * @return array
   *   The variables.
   */
  public function getNodeVariables(VaNodeInterface $node): array {
    $details = $node->getContentReleaseTriggerDetails();
    return [
      '%link_to_node' => $this->getNodeLink($node),
      '%nid' => $node->id(),
      '%type' => $node->getType(),
      '%details' => json_encode($details, JSON_PRETTY_PRINT),
    ];
  }

  /**
   * Log whether a content release would have been triggered by this change.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   */
  public function logTriggerDecision(VaNodeInterface $node) {
    $wouldHaveBeenTriggered = $node->shouldTriggerContentRelease();
    $variables = $this->getNodeVariables($node);
    $variables['@would'] = $wouldHaveBeenTriggered ? 'would have' : 'would not have';
    $message = $this->t('A content release @would been triggered by a change to %type: %link_to_node (node%nid) (%details).', $variables);
    $this->logger->info($message);
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool {
    $this->logTriggerDecision($node);
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getReasonMessage(VaNodeInterface $node) : string {
    return $this->t('This should never be reached because the strategy always indicates that no content release should be triggered.');
  }

}
