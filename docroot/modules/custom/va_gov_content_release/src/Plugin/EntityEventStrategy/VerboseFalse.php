<?php

namespace Drupal\va_gov_content_release\Plugin\EntityEventStrategy;

use Drupal\Component\Render\FormattableMarkup;
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
   * Calculate variables for a specified node.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   *
   * @return string
   *   The log message.
   */
  public function getMessage(VaNodeInterface $node): string {
    $wouldTrigger = $node->shouldTriggerContentRelease();
    $details = $node->getContentReleaseTriggerDetails();
    $prettyPrintedDetails = nl2br(json_encode($details, JSON_PRETTY_PRINT));
    $prettyPrintedDetails = new FormattableMarkup($prettyPrintedDetails, []);
    return $this->t('A content release %would have been triggered by a change to %type: %link_to_node (node %nid) (@details).', [
      '%link_to_node' => $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString(),
      '%nid' => $node->id(),
      '%type' => $node->getType(),
      '%would' => $wouldTrigger ? 'would' : 'would not',
      '@details' => $prettyPrintedDetails,
    ]);
  }

  /**
   * Log whether a content release would have been triggered by this change.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   */
  public function logTriggerDecision(VaNodeInterface $node) {
    $message = $this->getMessage($node);
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
