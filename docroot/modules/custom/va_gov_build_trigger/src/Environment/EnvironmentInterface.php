<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * EnvironmentInterface for Environment Class.
 */
interface EnvironmentInterface extends PluginInspectionInterface {

  /**
   * The URL for the front end.
   *
   * @return string
   *   The web site url.
   */
  public function getWebUrl() : string;

  /**
   * Trigger the frontend web build.
   */
  public function triggerFrontendBuild() : void;

  /**
   * Should this environment trigger a frontend content deploy?
   *
   * This controls whether content updates should trigger the
   * front end deploy.
   *
   * @return bool
   *  Should we trigger a front end deploy
   */
  public function shouldTriggerFrontendBuild() : bool;

}
