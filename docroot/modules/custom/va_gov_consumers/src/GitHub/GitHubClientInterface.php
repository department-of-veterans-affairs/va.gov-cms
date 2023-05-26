<?php

namespace Drupal\va_gov_consumers\GitHub;

/**
 * A service that provides access to the Github API.
 *
 * This is primarily used for triggering actions and repository dispatches.
 */
interface GitHubClientInterface {

  /**
   * Search Pull Requests.
   *
   * @param string $searchString
   *   Search string.
   * @param int $count
   *   Number of pull requests to return.
   *
   * @return array
   *   Array of pull request names.
   */
  public function searchPullRequests(string $searchString, int $count = 10) : array;

  /**
   * Manually trigger a workflow.
   *
   * @param string $workflowName
   *   The name of the workflow, e.g. 'deploy.yml'.
   * @param string $ref
   *   The branch, tag, or commitish that the action should run against.
   * @param array $params
   *   A list of named params to pass to the action as arguments. Keys should
   *   match action input names.
   */
  public function triggerWorkflow(string $workflowName, string $ref, array $params = []) : void;

  /**
   * List workflow runs for an action.
   *
   * @param string $workflowName
   *   The name of the workflow, e.g. 'deploy.yml'.
   * @param array $params
   *   A list of named params to pass to the action as arguments. Keys should
   *   match the action input names.
   */
  public function listWorkflowRuns(string $workflowName, array $params = []) : array;

  /**
   * Send a repository dispatch event.
   *
   * @param string $eventType
   *   A custom webhook event name. Must be 100 characters or fewer.
   * @param object $clientPayload
   *   Optional extra data to send as the payload with the dispatch.
   */
  public function repositoryDispatchWorkflow(string $eventType, object $clientPayload = NULL) : void;

}
