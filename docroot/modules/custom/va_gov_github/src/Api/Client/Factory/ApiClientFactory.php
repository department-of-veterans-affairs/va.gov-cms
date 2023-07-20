<?php

namespace Drupal\va_gov_github\Api\Client\Factory;

use Drupal\va_gov_github\Api\Client\ApiClient;
use Drupal\va_gov_github\Api\Client\ApiClientInterface;

/**
 * The GitHub Api Client Factory service.
 *
 * This service is used to create GitHub Api Client instances, and to make
 * these operations testable.
 */
class ApiClientFactory implements ApiClientFactoryInterface {

  /**
   * {@inheritDoc}
   */
  public function get(string $owner, string $repository, string $apiToken = NULL): ApiClientInterface {
    return new ApiClient($owner, $repository, $apiToken);
  }

}
