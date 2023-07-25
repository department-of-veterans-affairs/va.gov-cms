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
   * Safely get a detail about the node and its changes.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   * @param callable $callback
   *   The callback to invoke.
   * @param mixed $default
   *   The default value to return if the callback throws an exception.
   *
   * @return mixed
   *   The value returned by the callback, or the default value if the callback
   *   threw an exception.
   */
  protected function safelyGetNodeDetail(VaNodeInterface $node, callable $callback, $default = NULL) {
    try {
      return $callback($node);
    }
    catch (\Exception $e) {
      return $default;
    }
  }

  /**
   * Get details about the node and changes thereto.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node.
   *
   * @return array
   *   The details, as an associative array.
   */
  public function getNodeDetails(VaNodeInterface $node): array {
    $details = [
      'isFacility',
      'isModerated',
      'hasOriginal',
      'didChangeOperatingStatus',
      'alwaysTriggersContentRelease',
      'isModeratedAndPublished',
      'isModeratedAndTransitionedFromPublishedToArchived',
      'isUnmoderatedAndPublished',
      'isUnmoderatedAndWasPreviouslyPublished',
      'didTransitionFromPublishedToArchived',
      'isCmPublished',
      'isPublished',
      'isArchived',
      'isDraft',
      'wasPublished',
    ];
    $detailValues = [];
    foreach ($details as $detail) {
      $detailValues[$detail] = $this->safelyGetNodeDetail($node, function () use ($node, $detail) {
        return call_user_func([$node, $detail]);
      }, FALSE);
    }
    return [
      'isFacility' => $detailValues['isFacility'],
      'isModerated' => $detailValues['isModerated'],
      'hasOriginal' => $detailValues['hasOriginal'],
      'didChangeOperatingStatus' => $detailValues['isFacility'] && $detailValues['didChangeOperatingStatus'],
      'alwaysTriggersContentRelease' => $detailValues['alwaysTriggersContentRelease'],
      'isModeratedAndPublished' => $detailValues['isModeratedAndPublished'],
      'isModeratedAndTransitionedFromPublishedToArchived' => $detailValues['isModeratedAndTransitionedFromPublishedToArchived'],
      'isUnmoderatedAndPublished' => $detailValues['isUnmoderatedAndPublished'],
      'isUnmoderatedAndWasPreviouslyPublished' => $detailValues['isUnmoderatedAndWasPreviouslyPublished'],
      'didTransitionFromPublishedToArchived' => $detailValues['isModerated'] && $detailValues['hasOriginal'] && $detailValues['didTransitionFromPublishedToArchived'],
      'isCmPublished' => $detailValues['isModerated'] && $detailValues['isCmPublished'],
      'isPublished' => $detailValues['isPublished'],
      'isArchived' => $detailValues['isModerated'] && $detailValues['isArchived'],
      'isDraft' => $detailValues['isModerated'] && $detailValues['isDraft'],
      'wasPublished' => $detailValues['isModerated'] && $detailValues['hasOriginal'] && $detailValues['wasPublished'],
    ];
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
  public function calculateVariables(VaNodeInterface $node): array {
    $details = $this->getNodeDetails($node);
    return [
      '%link_to_node' => $node->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString(),
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
  public function logContentReleaseTriggerDecision(VaNodeInterface $node) {
    $wouldHaveBeenTriggered = $node->shouldTriggerContentRelease();
    $variables['@would'] = $wouldHaveBeenTriggered ? 'would have' : 'would not have';
    $message = $this->t('A content release @would been triggered by a change to %type: %link_to_node (node%nid) (%details).', $variables);
    $this->logger->info($message);
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
