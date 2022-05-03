<?php

namespace Drupal\va_gov_build_trigger\Service;

/**
 * The interface for the build requester service.
 */
interface BuildRequesterInterface {

  /**
   * Request a frontend build.
   *
   * @param string $reason
   *   The reason for requesting the build.
   */
  public function requestFrontendBuild(string $reason) : void;

  /**
   * Switch the version of the frontend to build.
   *
   * @param string $commitish
   *   A git commitish describing the version of the frontend to use.
   */
  public function switchFrontendVersion(string $commitish) : void;

  /**
   * Reset the version of the frontend to build to the default.
   */
  public function resetFrontendVersion() : void;

  /**
   * Request a scheduled build if appropriate.
   *
   * This method will do nothing if the following criteria are not met:
   *   * It is currently during business hours (ET)
   *   * The last scheduled build was more than 60 minutes ago.
   */
  public function checkScheduledBuild() : void;
}
