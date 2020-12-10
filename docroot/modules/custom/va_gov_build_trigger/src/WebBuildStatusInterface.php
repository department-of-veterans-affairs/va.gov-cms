<?php

namespace Drupal\va_gov_build_trigger;

/**
 * Status of Web Build.
 */
interface WebBuildStatusInterface {

  /**
   * Get the status of the web build.
   *
   * @return bool
   *   The status of the web build.
   */
  public function getWebBuildStatus() : bool;

  /**
   * Enable the Web Build status.
   */
  public function enableWebBuildStatus() : void;

  /**
   * Disable Web Build Status.
   */
  public function disableWebBuildStatus() : void;

}
