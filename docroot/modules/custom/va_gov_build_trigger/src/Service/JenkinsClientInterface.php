<?php

namespace Drupal\va_gov_build_trigger\Service;

use GuzzleHttp\ClientInterface;

/**
 * A client for interfacing with Jenkins.
 */
interface JenkinsClientInterface {

  /**
   * Get an HTTP client appropriate for making a request to Jenkins.
   *
   * @return \GuzzleHttp\ClientInterface
   *   A configured HTTP client.
   */
  public function getHttpClient(): ClientInterface;

  /**
   * Request a front end build.
   *
   * @param string $frontendGitRef
   *   The git ref of the frontend.
   * @param bool $fullRebuild
   *   Whether or not a full rebuild should be requested.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   An HTTP client.
   *
   * @throws \Drupal\va_gov_build_trigger\Exception\JenkinsClientException
   */
  public function requestFrontendBuild(string $frontendGitRef = NULL, bool $fullRebuild = FALSE, ClientInterface $httpClient = NULL): void;

}
