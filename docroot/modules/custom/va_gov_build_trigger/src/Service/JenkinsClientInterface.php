<?php

namespace Drupal\va_gov_build_trigger\Service;

/**
 * A client for interfacing with Jenkins.
 */
interface JenkinsClientInterface {

  /**
   * Request a front end build.
   *
   * @param string $frontendGitRef
   *   The git ref of the frontend.
   * @param bool $fullRebuild
   *   Whether or not a full rebuild should be requested.
   *
   * @throws \Drupal\va_gov_build_trigger\Exception\JenkinsClientException
   */
  public function requestFrontendBuild(string $frontendGitRef = NULL, bool $fullRebuild = FALSE): void;

}
