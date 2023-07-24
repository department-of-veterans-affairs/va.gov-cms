<?php

namespace Drupal\va_gov_github\Api\Client;

/**
 * A service that provides access to the Github API for a specific repository.
 *
 * This is primarily used for triggering actions and repository dispatches.
 */
interface ApiClientInterface {

  /**
   * Make a raw GET request.
   *
   * @param string $route
   *   The route.
   * @param array $headers
   *   The headers.
   *
   * @return array|string
   *   The response.
   */
  public function get(string $route, array $headers = []): array|string;

  /**
   * Get workflow runs for an action.
   *
   * @param string $workflowName
   *   The name of the workflow, e.g. 'deploy.yml'.
   * @param array $parameters
   *   A list of named parameters to pass to the action as arguments. Keys
   *   should match the action input names.
   *
   * @return array
   *   The API response. The 'workflow_runs' key contains an array of workflow
   *   runs.
   */
  public function getWorkflowRuns(string $workflowName, array $parameters = []) : array;

  /**
   * Search issues and pull requests.
   *
   * @param string $search
   *   The search string.
   * @param string $sortField
   *   The sort field.
   * @param string $order
   *   The sort order ('asc' or 'desc').
   *
   * @return array
   *   The API response.
   */
  public function searchIssues(string $search, string $sortField = 'updated', string $order = 'desc') : array;

  /**
   * Search pull requests.
   *
   * @param string $search
   *   The search string.
   * @param string $sortField
   *   The sort field.
   * @param string $order
   *   The sort order ('asc' or 'desc').
   *
   * @return array
   *   The API response.
   */
  public function searchPullRequests(string $search, string $sortField = 'updated', string $order = 'desc') : array;

  /**
   * Trigger a repository dispatch event.
   *
   * Do NOT confuse this with a workflow dispatch event. This is for
   * workflows with a `repository_dispatch` trigger. If the workflow instead has
   * a `workflow_dispatch` trigger, use `triggerWorkflowDispatchEvent()`.
   *
   * This can and will fail silently if the workflow does not have a
   * `repository_dispatch` trigger.
   *
   * The token will need the following permissions:
   * - `repo`
   *
   * @param string $eventType
   *   The type of event to trigger.
   * @param array $parameters
   *   A list of named parameters to pass to the action as arguments. Keys
   *   should match the action input names.
   *
   * @throws \Drupal\va_gov_github\Exception\RepositoryDispatchException
   *   If the workflow dispatch fails.
   *
   * @see https://docs.github.com/en/rest/actions/workflows?apiVersion=2022-11-28#create-a-workflow-dispatch-event
   */
  public function triggerRepositoryDispatchEvent(string $eventType, array $parameters = []) : void;

  /**
   * Trigger a workflow dispatch event.
   *
   * Do NOT confuse this with a repository dispatch event. This is for
   * workflows with a `workflow_dispatch` trigger. If the workflow instead has
   * a `repository_dispatch` trigger, use `triggerRepositoryDispatchEvent()`.
   *
   * This can and will fail silently if the workflow does not have a
   * `workflow_dispatch` trigger.
   *
   * The token will need the following permissions:
   * - `repo`
   *
   * @param string $workflowId
   *   The ID of the workflow to trigger.
   * @param string $reference
   *   The branch, tag, or sha to trigger the workflow on.
   * @param array $parameters
   *   A list of named parameters to pass to the action as arguments. Keys
   *   should match the action input names.
   *
   * @throws \Drupal\va_gov_github\Exception\WorkflowDispatchException
   *   If the workflow dispatch fails.
   *
   * @see https://docs.github.com/en/rest/actions/workflows?apiVersion=2022-11-28#create-a-workflow-dispatch-event
   */
  public function triggerWorkflowDispatchEvent(string $workflowId, string $reference = 'main', array $parameters = []) : void;

}
