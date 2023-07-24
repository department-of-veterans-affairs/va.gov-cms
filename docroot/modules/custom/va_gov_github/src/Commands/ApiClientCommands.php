<?php

namespace Drupal\va_gov_github\Commands;

use Drupal\va_gov_github\Api\Client\Factory\ApiClientFactoryInterface;
use Drupal\va_gov_github\Api\Client\ApiClientInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush interface to the API client.
 */
class ApiClientCommands extends DrushCommands {

  const WORKFLOW_RUN_COLUMN_HEADERS = [
    'id',
    'name',
    'event',
    'status',
    'conclusion',
    'created_at',
    'updated_at',
  ];

  const ISSUE_COLUMN_HEADERS = [
    'id',
    'number',
    'title',
    'state',
  ];

  const PULL_REQUEST_COLUMN_HEADERS = [
    'id',
    'number',
    'title',
    'state',
  ];

  /**
   * The API client factory.
   *
   * @var \Drupal\va_gov_github\Api\Client\Factory\ApiClientFactoryInterface
   */
  protected $apiClientFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_github\Api\Client\Factory\ApiClientFactoryInterface $apiClientFactory
   *   The API client factory.
   */
  public function __construct(
    ApiClientFactoryInterface $apiClientFactory
  ) {
    $this->apiClientFactory = $apiClientFactory;
  }

  /**
   * Get the API client.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $apiToken
   *   The API token.
   *
   * @return \Drupal\va_gov_github\Api\Client\ApiClientInterface
   *   The API client.
   */
  protected function getApiClient(string $owner, string $repository, string $apiToken = ''): ApiClientInterface {
    return $this->apiClientFactory->get($owner, $repository, $apiToken);
  }

  /**
   * List workflow runs for a repository and workflow.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $workflowId
   *   The workflow ID.
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:api-client:workflow-runs
   * @aliases va-gov-github-api-client-workflow-runs
   */
  public function listWorkflowRuns(
    string $owner,
    string $repository,
    string $workflowId,
    string $apiToken = ''
  ) {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $workflowRuns = $apiClient->getWorkflowRuns($workflowId);
    $workflowRuns = array_map(function ($workflowRun) {
      return [
        $workflowRun['id'],
        $workflowRun['name'],
        $workflowRun['event'],
        $workflowRun['status'],
        $workflowRun['conclusion'],
        $workflowRun['created_at'],
        $workflowRun['updated_at'],
      ];
    }, $workflowRuns['workflow_runs']);
    $this->io()->table(static::WORKFLOW_RUN_COLUMN_HEADERS, $workflowRuns);
  }

  /**
   * Search issues for a repository.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $searchString
   *   The search string.
   * @param string $apiToken
   *   The API token.
   * @param string $sortField
   *   The sort field.
   * @param string $sortOrder
   *   The sort order.
   *
   * @command va-gov-github:api-client:search-issues
   * @aliases va-gov-github-api-client-search-issues
   */
  public function searchIssues(
    string $owner,
    string $repository,
    string $searchString,
    string $apiToken = '',
    string $sortField = 'updated',
    string $sortOrder = 'desc'
  ) {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $issues = $apiClient->searchIssues($searchString, $sortField, $sortOrder);
    $maxTitleLength = 90;
    $issues = array_map(function ($issue) use ($maxTitleLength) {
      return [
        $issue['id'],
        $issue['number'],
        strlen($issue['title']) <= $maxTitleLength ? $issue['title'] : substr($issue['title'], 0, $maxTitleLength - 3) . '...',
        $issue['state'],
      ];
    }, $issues['items']);
    $this->io()->table(static::ISSUE_COLUMN_HEADERS, $issues);
  }

  /**
   * Search pull requests for a repository.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $searchString
   *   The search string.
   * @param string $apiToken
   *   The API token.
   * @param string $sortField
   *   The sort field.
   * @param string $sortOrder
   *   The sort order.
   *
   * @command va-gov-github:api-client:search-pull-requests
   * @aliases va-gov-github-api-client-search-pull-requests
   *   va-gov-github:api-client:search-prs
   *   va-gov-github-api-client-search-prs
   */
  public function searchPullRequests(
    string $owner,
    string $repository,
    string $searchString,
    string $apiToken = '',
    string $sortField = 'updated',
    string $sortOrder = 'desc'
  ) {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $pullRequests = $apiClient->searchPullRequests($searchString, $sortField, $sortOrder);
    $maxTitleLength = 90;
    $pullRequests = array_map(function ($pullRequest) use ($maxTitleLength) {
      return [
        $pullRequest['id'],
        $pullRequest['number'],
        strlen($pullRequest['title']) <= $maxTitleLength ? $pullRequest['title'] : substr($pullRequest['title'], 0, $maxTitleLength - 3) . '...',
        $pullRequest['state'],
      ];
    }, $pullRequests['items']);
    $this->io()->table(static::ISSUE_COLUMN_HEADERS, $pullRequests);
  }

  /**
   * Send a repository dispatch event.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $eventType
   *   The event type.
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:api-client:repository-dispatch
   * @aliases va-gov-github-api-client-repository-dispatch
   */
  public function repositoryDispatch(
    string $owner,
    string $repository,
    string $eventType,
    string $apiToken = ''
  ) {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $apiClient->triggerRepositoryDispatchEvent($eventType);
    $this->io()->success('Repository dispatch event sent.');
  }

  /**
   * Send a workflow dispatch event.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $workflowId
   *   The workflow ID.
   * @param string $reference
   *   The git commit, tag, branch, etc.
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:api-client:workflow-dispatch
   * @aliases va-gov-github-api-client-workflow-dispatch
   */
  public function workflowDispatch(
    string $owner,
    string $repository,
    string $workflowId,
    string $reference = 'main',
    string $apiToken = ''
  ) {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $apiClient->triggerWorkflowDispatchEvent($workflowId, $reference);
    $this->io()->success('Workflow dispatch event sent.');
  }

}
