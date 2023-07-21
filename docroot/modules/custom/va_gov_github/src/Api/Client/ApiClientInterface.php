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
   */
  public function getWorkflowRuns(string $workflowName, array $parameters = []) : array;

}
