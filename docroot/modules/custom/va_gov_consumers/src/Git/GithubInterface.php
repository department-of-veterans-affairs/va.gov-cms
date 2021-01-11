<?php

namespace Drupal\va_gov_consumers\Git;

/**
 * Access to the Github  API.
 */
interface GithubInterface {

  /**
   * Search Pull Requests.
   *
   * @param string $search_on
   *   Search string.
   * @param int $count
   *   Number of pull requests to return.
   *
   * @return array
   *   Array of pull request names.
   */
  public function searchPullRequests(string $search_on, int $count = 10) : array;

}
