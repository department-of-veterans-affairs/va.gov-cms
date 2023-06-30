<?php

namespace Drupal\va_gov_content_release\GitHub;

use Drupal\va_gov_consumers\GitHub\GitHubClientInterface;
use Drupal\va_gov_content_release\Exception\ContentReleaseInProgressException;
use Drupal\va_gov_content_release\Exception\GitHubRepositoryDispatchException;

/**
 * The GitHub repository dispatch service.
 *
 * This service is used to dispatch repository dispatch events to GitHub, to
 * check whether a current workflow is pending, and to make these operations
 * testable.
 *
 * @see \Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatchInterface
 */
class GitHubRepositoryDispatch implements GitHubRepositoryDispatchInterface {

  /**
   * The GitHub client.
   *
   * @var \Drupal\va_gov_consumers\GitHub\GitHubClientInterface
   */
  protected $client;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_consumers\GitHub\GitHubClientInterface $client
   *   The GitHub client.
   */
  public function __construct(GitHubClientInterface $client) {
    $this->client = $client;
  }

  /**
   * Build parameters for determining whether a workflow is pending.
   *
   * @param int|null $time
   *   The time to use for the 'created' parameter. Defaults to the current
   *   time.
   *
   * @return array
   *   The parameters.
   */
  public function buildPendingWorkflowParams(int $time = NULL) : array {
    $time = $time ?? time();
    $sinceTimestamp = $time - (2 * 60 * 60);
    return [
      'status' => 'pending',
      'created' => '>=' . date('c', $sinceTimestamp),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function dispatch() : void {
    try {
      if ($this->isPending()) {
        throw new ContentReleaseInProgressException('A workflow is already pending.');
      }
      $this->client->repositoryDispatchWorkflow(static::EVENT_TYPE);
    }
    catch (ContentReleaseInProgressException $exception) {
      throw $exception;
    }
    catch (\Throwable $throwable) {
      throw new GitHubRepositoryDispatchException('Repository dispatch failed.', $throwable->getCode(), $throwable);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function isPending() : bool {
    try {
      $parameters = $this->buildPendingWorkflowParams();
      $workflowRuns = $this->client->listWorkflowRuns(static::EVENT_TYPE . '.yml', $parameters);
      return !empty($workflowRuns['total_count']) && $workflowRuns['total_count'] > 0;
    }
    catch (\Throwable $throwable) {
      throw new GitHubRepositoryDispatchException('Failed to get workflow runs.', $throwable->getCode(), $throwable);
    }
  }

}
