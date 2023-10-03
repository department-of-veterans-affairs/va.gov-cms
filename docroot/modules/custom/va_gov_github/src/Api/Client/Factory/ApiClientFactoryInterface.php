<?php

namespace Drupal\va_gov_github\Api\Client\Factory;

use Drupal\va_gov_github\Api\Client\ApiClientInterface;

/**
 * An interface for the GitHub Api Client Factory service.
 *
 * This service is used to create GitHub Api Client instances, and to make
 * these operations testable.
 */
interface ApiClientFactoryInterface {

  /**
   * Creates a GitHub Api Client instance.
   *
   * If the API token is not provided, the client will still be created and
   * returned. However, later calls to the client will fail if the API token is
   * required.
   *
   * @param string $owner
   *   The GitHub repository owner.
   * @param string $repository
   *   The GitHub repository name.
   * @param string $apiToken
   *   The GitHub API token.
   *
   * @return \Drupal\va_gov_github\Api\Client\ApiClientInterface
   *   The GitHub Api Client instance.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function get(string $owner, string $repository, string $apiToken = NULL): ApiClientInterface;

  /**
   * Retrieve an API client for the VA.gov-CMS repository.
   *
   * @return \Drupal\va_gov_github\Api\Client\ApiClientInterface
   *   The GitHub Api Client instance.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function getCms(): ApiClientInterface;

  /**
   * Retrieve an API client for the Content Build repository.
   *
   * @return \Drupal\va_gov_github\Api\Client\ApiClientInterface
   *   The GitHub Api Client instance.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function getContentBuild(): ApiClientInterface;

  /**
   * Retrieve an API client for the Vets-Website repository.
   *
   * @return \Drupal\va_gov_github\Api\Client\ApiClientInterface
   *   The GitHub Api Client instance.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function getVetsWebsite(): ApiClientInterface;

}
