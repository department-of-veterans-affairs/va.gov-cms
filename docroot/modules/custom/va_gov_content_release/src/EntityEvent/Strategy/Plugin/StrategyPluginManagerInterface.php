<?php

namespace Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin;

use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * An interface for the entity event strategy plugin manager.
 */
interface StrategyPluginManagerInterface {

  /**
   * Get the strategy plugin.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @return \Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginInterface
   *   The strategy plugin.
   *
   * @throws \Drupal\va_gov_content_release\Exception\StrategyErrorException
   *   If the strategy cannot be found.
   */
  public function getStrategy(string $id) : StrategyPluginInterface;

  /**
   * Determine whether we should trigger a content release.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node that triggered the event.
   *
   * @throws \Drupal\va_gov_content_release\Exception\UnknownStrategyException
   *   If the strategy cannot be found.
   * @throws \Drupal\va_gov_content_release\Exception\StrategyErrorException
   *   If the strategy encounters an error.
   */
  public function shouldTriggerContentRelease(string $id, VaNodeInterface $node) : bool;

}
