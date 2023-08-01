<?php

namespace Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * An interface for Entity Event Strategy plugins.
 *
 * These plugins are used to determine if a content release should be triggered
 * based on the environment and the entity event.
 */
interface StrategyPluginInterface extends PluginInspectionInterface {

  /**
   * Determine whether we should trigger a content release.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node that triggered the event.
   *
   * @return bool
   *   TRUE if we should trigger a content release, FALSE otherwise.
   */
  public function shouldTriggerContentRelease(VaNodeInterface $node) : bool;

  /**
   * Populate a reason message for triggering a content release.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node that triggered the event.
   *
   * @return string
   *   The reason message.
   */
  public function getReasonMessage(VaNodeInterface $node) : string;

}
