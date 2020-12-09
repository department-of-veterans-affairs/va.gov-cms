<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * EnvironmentInterface for Environment Class.
 */
interface EnvironmentInterface extends PluginInspectionInterface {

  /**
   * The URL the the front end.
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
   * Should this environment trigger an front content deploy?
   *
   * This controls if content updates shoudl trigger the
   * front end deploy?
   *
   * @reutrn bool
   *  Should we trigger a front end deploy
   */
  public function shouldTriggerFrontendBuild() : bool;

}
