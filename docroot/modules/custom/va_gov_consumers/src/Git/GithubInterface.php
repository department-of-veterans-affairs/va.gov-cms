<?php

namespace Drupal\va_gov_consumers\Git;

/**
 * Access to the Github  API.
 */
interface GithubInterface {
  /**
   * Search branches.
   *
   * @param string $string_on
   *   Search string.
   * @param int $count
   *   Number of pull requests to return.
   *
   * @return array
   *   Array of pull request names.
   */
  public function searchPullRequests(string $search_on, int $count = 10) : array;

}
