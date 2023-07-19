<?php

namespace Drupal\va_gov_git\BranchSearch;

/**
 * Interface for the branch search service.
 */
interface BranchSearchInterface {

  /**
   * Get the list of remote branches on the specified remote.
   *
   * @param string $remote
   *   The remote to search.
   *
   * @return string[]
   *   The list of branches on the specified remote.
   */
  public function getRemoteBranchNames(string $remote = 'origin'): array;

  /**
   * Get the list of remote branches containing the given string.
   *
   * @param string $string
   *   The string to search for.
   * @param string $remote
   *   The remote to search.
   *
   * @return string[]
   *   The list of branches containing the given string.
   */
  public function getRemoteBranchNamesContaining(string $string, string $remote = 'origin'): array;

}
