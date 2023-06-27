<?php

namespace Drupal\va_gov_consumers\GitHub;

use Drupal\va_gov_consumers\Exception\GitHubRepositoryDispatchException;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A service that provides access to the Github API.
 *
 * This is primarily used for triggering actions and repository dispatches.
 */
class GitHubClient implements GitHubClientInterface {

  /**
   * The GitHub repo path, e.g. 'department-of-veterans-affairs/va.gov-cms'.
   *
   * @var string
   */
  protected $repositoryPath;

  /**
   * The GitHub token used to authenticate requests.
   *
   * @var string
   */
  protected $token;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * GitHubClient constructor.
   *
   * @param string $repositoryPath
   *   The repo path, e.g. 'department-of-veterans-affairs/va.gov-cms'.
   * @param string $token
   *   The GitHub token used to authenticate requests.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(string $repositoryPath, string $token, HttpClientInterface $httpClient) {
    $this->repositoryPath = $repositoryPath;
    $this->token = $token;
    $this->httpClient = $httpClient;
  }

  /**
   * Make a generic GitHub API request.
   *
   * @param string $method
   *   The HTTP method, e.g. 'GET'.
   * @param string $uri
   *   The URI to request.
   * @param array $options
   *   The request options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function request(string $method, string $uri, array $options = []) {
    $options['headers']['Authorization'] = "Bearer {$this->token}";
    $options['headers']['Accept'] = 'application/vnd.github.full+json';
    $options['headers']['User-Agent'] = 'va.gov-cms';
    $options['base_uri'] = "https://api.github.com/";
    return $this->httpClient->request($method, $uri, $options);
  }

  /**
   * Return the raw response from searching for a pull request.
   *
   * @param string $searchString
   *   Search string.
   * @param int $count
   *   Number of pull requests to return.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function searchPullRequestsRaw(string $searchString, int $count = 10): ResponseInterface {
    return $this->request('GET', 'search/issues', [
      'query' => [
        'q' => "is:pull-request repo:{$this->repositoryPath} {$searchString}",
        'per_page' => $count,
      ],
    ]);
  }

  /**
   * Return the raw response from triggering a workflow.
   *
   * @param string $workflowName
   *   The name of the workflow, e.g. 'deploy.yml'.
   * @param string $ref
   *   The branch to trigger the action on.
   * @param array $params
   *   The parameters to pass to the action.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function triggerWorkflowRaw(string $workflowName, string $ref, array $params = []) : ResponseInterface {
    return $this->request('POST', "repos/{$this->repositoryPath}/actions/workflows/{$workflowName}/dispatches", [
      'json' => [
        'ref' => $ref,
        'inputs' => (object) $params,
      ],
    ]);
  }

  /**
   * List workflow runs for an action.
   *
   * @param string $workflowName
   *   The name of the workflow, e.g. 'deploy.yml'.
   * @param array $params
   *   A list of named params to pass to the action as arguments. Keys should
   *   match the action input names.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function listWorkflowRunsRaw(string $workflowName, array $params = []) : ResponseInterface {
    return $this->request('GET', "repos/{$this->repositoryPath}/actions/workflows/{$workflowName}/runs", [
      'query' => $params,
    ]);
  }

  /**
   * Create a repository dispatch event.
   *
   * @param string $eventType
   *   A custom webhook event name. Must be 100 characters or fewer.
   * @param object $clientPayload
   *   Optional extra data to send as the payload with the event.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  public function repositoryDispatchWorkflowRaw(string $eventType, object $clientPayload = NULL) : ResponseInterface {
    return $this->request('POST', "repos/{$this->repositoryPath}/dispatches", [
      'json' => [
        'event_type' => $eventType,
        'client_payload' => $clientPayload ?? new \stdClass(),
      ],
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function searchPullRequests(string $searchString, int $count = 10) : array {
    $response = $this->searchPullRequestsRaw($searchString, $count);
    $data = json_decode($response->getBody()->getContents(), TRUE);
    if ($data === NULL) {
      throw new \Exception('The GitHub API returned an invalid JSON response.');
    }
    return $data;
  }

  /**
   * {@inheritDoc}
   */
  public function triggerWorkflow(string $workflowName, string $ref, array $params = []) : void {
    $this->triggerWorkflowRaw($workflowName, $ref, $params);
  }

  /**
   * {@inheritDoc}
   */
  public function listWorkflowRuns(string $workflowName, array $params = []) : array {
    try {
      $response = $this->listWorkflowRunsRaw($workflowName, $params);
      if ($response->getStatusCode() !== 200) {
        throw new GitHubRepositoryDispatchException('Listing workflow runs failed with status code: ' . $response->getStatusCode());
      }
      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data;
    }
    catch (\Throwable $exception) {
      throw new GitHubRepositoryDispatchException('Listing workflow runs failed.', $exception->getCode(), $exception);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function repositoryDispatchWorkflow(string $eventType, object $clientPayload = NULL) : void {
    try {
      $response = $this->repositoryDispatchWorkflowRaw($eventType, $clientPayload);
      if ($response->getStatusCode() !== 204) {
        throw new GitHubRepositoryDispatchException('Repository dispatch failed with status code: ' . $response->getStatusCode());
      }
    }
    catch (\Throwable $exception) {
      throw new GitHubRepositoryDispatchException('Repository dispatch failed.', $exception->getCode(), $exception);
    }
  }

}
