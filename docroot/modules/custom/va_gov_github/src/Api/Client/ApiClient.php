<?php

namespace Drupal\va_gov_github\Api\Client;

use Drupal\va_gov_github\Exception\InvalidApiTokenException;
use Drupal\va_gov_github\Exception\RepositoryDispatchException;
use Drupal\va_gov_github\Exception\WorkflowDispatchException;
use Github\AuthMethod;
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
   * Create an instance with the specified raw client.
   *
   * @param \Github\Client $rawClient
   *   The raw client.
   * @param string $owner
   *   The GitHub repository owner.
   * @param string $repository
   *   The GitHub repository name.
   * @param string $token
   *   The GitHub API token.
   *
   * @return static
   *   The instance.
   */
  public static function createWithRawApiClient(
    RawApiClient $rawClient,
    string $owner,
    string $repository,
    string $token = NULL
  ): self {
    $instance = new static($owner, $repository, $token);
    $instance->rawClient = $rawClient;
    return $instance;
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
        $this->rawClient->authenticate($this->token, NULL, AuthMethod::ACCESS_TOKEN);
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

  /**
   * {@inheritdoc}
   */
  public function getWorkflowRuns(string $actionName, array $parameters = []): array {
    return $this->rawClient->repositories()->workflowRuns()->listRuns(
      $this->owner,
      $this->repository,
      $actionName,
      $parameters
    );
  }

  /**
   * {@inheritdoc}
   */
  public function searchIssues(string $search, string $sortField = 'updated', string $order = 'desc') : array {
    $expression = "is:issue repo:{$this->owner}/{$this->repository} {$search}";
    return $this->rawClient->search()->issues($expression, $sortField, $order);
  }

  /**
   * {@inheritdoc}
   */
  public function searchPullRequests(string $search, string $sortField = 'updated', string $order = 'desc') : array {
    $expression = "is:pr repo:{$this->owner}/{$this->repository} {$search}";
    return $this->rawClient->search()->issues($expression, $sortField, $order);
  }

  /**
   * {@inheritdoc}
   */
  public function triggerRepositoryDispatchEvent(string $eventType, array $parameters = []) : void {
    try {
      $this->rawClient->repositories()->dispatch(
        $this->owner,
        $this->repository,
        $eventType,
        $parameters
      );
    }
    catch (\Throwable $exception) {
      throw new RepositoryDispatchException('Error triggering repository dispatch: ' . $exception->getMessage(), 0, $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function triggerWorkflowDispatchEvent(string $workflowId, string $reference = 'main', array $parameters = []) : void {
    try {
      $this->rawClient->repositories()->workflows()->dispatches(
        $this->owner,
        $this->repository,
        $workflowId,
        $reference,
        $parameters
      );
    }
    catch (\Throwable $exception) {
      throw new WorkflowDispatchException('Error triggering workflow dispatch: ' . $exception->getMessage(), 0, $exception);
    }
  }

}
