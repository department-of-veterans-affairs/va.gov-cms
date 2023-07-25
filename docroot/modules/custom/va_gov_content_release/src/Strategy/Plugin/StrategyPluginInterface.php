<?php

namespace Drupal\va_gov_content_release\Strategy\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface for strategy plugins.
 */
interface StrategyPluginInterface extends PluginInspectionInterface {

  /**
   * Trigger the content release.
   *
   * @throws \Drupal\va_gov_content_release\Exception\StrategyErrorException
   *   If the strategy cannot trigger a content release.
   */
  public function triggerContentRelease() : void;

}
