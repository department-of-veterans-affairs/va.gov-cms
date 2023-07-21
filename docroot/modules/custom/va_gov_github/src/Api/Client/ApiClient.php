<?php

namespace Drupal\va_gov_github\Api\Client;

use Drupal\va_gov_github\Exception\InvalidApiTokenException;

use Github\Client as RawApiClient;
use Github\HttpClient\Message\ResponseMediator as RawApiClientResponseMediator;
use Psr\Http\Message\ResponseInterface;

/**
 * A service that provides access to the Github API for a specific repository.
 *
 * This is primarily used for triggering actions and repository dispatches.
 */
class ApiClient implements ApiClientInterface {

  /**
   * The GitHub repository owner, e.g. 'department-of-veterans-affairs'.
   *
   * @var string
   */
  protected $owner;

  /**
   * The GitHub repository name, e.g. 'va.gov-cms'.
   *
   * @var string
   */
  protected $repository;

  /**
   * The GitHub token used to authenticate requests.
   *
   * @var string
   */
  protected $token;

  /**
   * The raw API client.
   *
   * @var \Github\Client
   */
  protected $rawClient;

  /**
   * The constructor.
   *
   * @param string $owner
   *   The GitHub repository owner.
   * @param string $repository
   *   The GitHub repository name.
   * @param string $token
   *   The GitHub API token.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function __construct(string $owner, string $repository, string $token = NULL) {
    $this->owner = $owner;
    $this->repository = $repository;
    $this->token = $token;
    $this->rawClient = new RawApiClient();
    $this->authenticate();
  }

  /**
   * Attempt authentication with the GitHub API.
   *
   * @throws \Drupal\va_gov_github\Exception\InvalidApiTokenException
   *   If the GitHub API token is provided, but is invalid.
   */
  public function authenticate(): void {
    if ($this->token) {
      try {
        $this->rawClient->authenticate($this->token, NULL, RawApiClient::AUTH_ACCESS_TOKEN);
      }
      catch (\Throwable $exception) {
        throw new InvalidApiTokenException('Error Authenticating: ' . $exception->getMessage(), 0, $exception);
      }
    }
  }

  /**
   * Get the raw API client.
   *
   * @return \Github\Client
   *   The raw API client.
   */
  public function getRawClient(): RawApiClient {
    return $this->rawClient;
  }

  /**
   * Retrieve the content of a raw GET request.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response.
   *
   * @return array|string
   *   The content.
   */
  public function getContent(ResponseInterface $response): array|string {
    return RawApiClientResponseMediator::getContent($response);
  }

  /**
   * {@inheritDoc}
   */
  public function get(string $route, array $headers = []): array|string {
    $response = $this->rawClient->getHttpClient()->get($route, $headers);
    return $this->getContent($response);
  }

}
