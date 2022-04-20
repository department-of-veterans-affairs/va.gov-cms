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
   *
   * @param string $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL) : void;

  /**
   * Should this environment trigger a frontend content deploy?
   *
   * This controls whether content updates should trigger the
   * front end deploy.
   *
   * @return bool
   *   Should we trigger a front end deploy
   */
  public function contentEditsShouldTriggerFrontendBuild() : bool;

  /**
   * The Build Trigger Form Class.
   *
   * @return string
   *   The build trigger form class
   */
  public function getBuildTriggerFormClass() : string;

}
