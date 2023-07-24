<?php

namespace Drupal\va_gov_github\Commands;

use Drush\Commands\DrushCommands;
use Github\AuthMethod;
use Github\Client as RawApiClient;
use Github\HttpClient\Message\ResponseMediator;

/**
 * A Drush interface to the raw GitHub API client.
 */
class RawApiClientCommands extends DrushCommands {

  /**
   * Get the API client.
   *
   * @param string $apiToken
   *   The API token.
   *
   * @return \Github\Client
   *   The raw API client.
   */
  protected function getApiClient(string $apiToken = ''): RawApiClient {
    $client = new RawApiClient();
    if ($apiToken) {
      $client->authenticate($apiToken, NULL, AuthMethod::ACCESS_TOKEN);
    }
    return $client;
  }

  /**
   * Request any route.
   *
   * @param string $route
   *   The route.
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:raw-api-client:request:get
   * @aliases va-gov-github-raw-api-client-request-get
   *   va-gov-github:raw-api-client:request
   *   va-gov-github-raw-api-client-request
   */
  public function request(string $route, string $apiToken = '') {
    $apiClient = $this->getApiClient($apiToken);
    $response = $apiClient->getHttpClient()->get($route);
    $this->io()->writeln(json_encode(ResponseMediator::getContent($response), JSON_PRETTY_PRINT));
  }

  /**
   * List repositories accessible by the current user.
   *
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:raw-api-client:current-user:repositories
   * @aliases va-gov-github-raw-api-client-current-user-repositories
   */
  public function listRepositories(string $apiToken = '') {
    $apiClient = $this->getApiClient($apiToken);
    $repositories = $apiClient->currentUser()->repositories();
    $repositoryNames = array_map(function ($repository) {
      return $repository['full_name'];
    }, $repositories);
    $this->io()->listing($repositoryNames);
  }

  /**
   * List organizational memberships of the current user.
   *
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:raw-api-client:current-user:organizations
   * @aliases va-gov-github-raw-api-client-current-user-organizations
   */
  public function listOrganizations(string $apiToken = '') {
    $apiClient = $this->getApiClient($apiToken);
    $organizations = $apiClient->currentUser()->memberships()->all();
    $organizationNames = array_map(function ($organization) {
      return $organization['organization']['login'];
    }, $organizations);
    $this->io()->listing($organizationNames);
  }

}
