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
    'html_url',
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
   * Get an API client.
   *
   * @param string $owner
   *   The owner.
   * @param string $repository
   *   The repository.
   * @param string $apiToken
   *   The API token.
   *
   * @command va-gov-github:api-client:get
   * @aliases va-gov-github-api-client-get
   */
  public function get(string $owner, string $repository, string $apiToken = '') {
    $this->io()->success('It worked!');
  }

  /**
   * List repositories accessible by the current user.
   *
   * @command va-gov-github:api-client:current-user:repositories
   * @aliases va-gov-github-api-client-current-user-repositories
   */
  public function listRepositories(string $owner, string $repository, string $apiToken = '') {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $repositories = $apiClient->getRawClient()->currentUser()->repositories();
    $repositoryNames = array_map(function ($repository) {
      return $repository['full_name'];
    }, $repositories);
    $this->io()->listing($repositoryNames);
  }

  /**
   * List organizational memberships of the current user.
   *
   * @command va-gov-github:api-client:current-user:organizations
   * @aliases va-gov-github-api-client-current-user-organizations
   */
  public function listOrganizations(string $owner, string $repository, string $apiToken = '') {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $organizations = $apiClient->getRawClient()->currentUser()->memberships()->all();
    $organizationNames = array_map(function ($organization) {
      return $organization['organization']['login'];
    }, $organizations);
    $this->io()->listing($organizationNames);
  }

  /**
   * List workflow runs for a repository and workflow.
   *
   * @command va-gov-github:api-client:workflow-runs
   * @aliases va-gov-github-api-client-workflow-runs
   */
  public function listWorkflowRuns(string $owner, string $repository, string $workflow, string $apiToken = '') {
    $apiClient = $this->getApiClient($owner, $repository, $apiToken);
    $workflowRuns = $apiClient->getWorkflowRuns($workflow);
    print json_encode($workflowRuns, JSON_PRETTY_PRINT);
    $workflowRuns = array_map(function ($workflowRun) {
      return [
        $workflowRun['id'],
        $workflowRun['name'],
        $workflowRun['event'],
        $workflowRun['status'],
        $workflowRun['conclusion'],
        $workflowRun['created_at'],
        $workflowRun['updated_at'],
        $workflowRun['html_url'],
      ];
    }, $workflowRuns['workflow_runs']);
    $this->io()->table(static::WORKFLOW_RUN_COLUMN_HEADERS, $workflowRuns);
  }

}
