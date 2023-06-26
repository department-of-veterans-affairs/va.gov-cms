<?php

namespace Drupal\va_gov_content_release\Strategy\Plugin;

/**
 * An interface for the strategy plugin manager.
 */
interface StrategyPluginManagerInterface {

  /**
   * Get the strategy plugin.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @return \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginInterface
   *   The strategy plugin.
   *
   * @throws \Drupal\va_gov_content_release\Exception\StrategyErrorException
   *   If the strategy cannot be found.
   */
  public function getStrategy(string $id) : StrategyPluginInterface;

  /**
   * Trigger the content release using the specified strategy.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @throws \Drupal\va_gov_content_release\Exception\UnknownStrategyException
   *   If the strategy cannot be found.
   * @throws \Drupal\va_gov_content_release\Exception\StrategyErrorException
   *   If the strategy cannot trigger a content release.
   */
  public function triggerContentRelease(string $id) : void;

}
