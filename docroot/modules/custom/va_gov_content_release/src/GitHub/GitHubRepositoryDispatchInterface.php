<?php

namespace Drupal\va_gov_content_release\GitHub;

/**
 * An interface for the GitHub repository dispatch service.
 *
 * This service is used to dispatch repository dispatch events to GitHub, to
 * check whether a current workflow is pending, and to make these operations
 * testable.
 *
 * @see \Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatch
 */
interface GitHubRepositoryDispatchInterface {

  // The event type for content release.
  const EVENT_TYPE = 'content-release';

  /**
   * Dispatch a repository dispatch event to trigger content release.
   *
   * @throws \Drupal\va_gov_content_release\Exception\GitHubRepositoryDispatchException
   *   If the repository dispatch fails.
   */
  public function dispatch() : void;

  /**
   * Check whether a workflow is pending.
   *
   * @return bool
   *   TRUE if a workflow is pending, FALSE otherwise.
   *
   * @throws \Drupal\va_gov_content_release\Exception\GitHubRepositoryDispatchException
   *   If the request fails.
   */
  public function isPending() : bool;

}
