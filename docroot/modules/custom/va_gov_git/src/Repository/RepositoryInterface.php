<?php

namespace Drupal\va_gov_git\Repository;

/**
 * Interface for repository objects.
 *
 * This wraps a repository object, likely vended by some third-party code.
 */
interface RepositoryInterface {

  /**
   * Get the name of this repository.
   *
   * @return string
   *   The name of this repository.
   */
  public function getName(): string;

  /**
   * Get the path to this repository.
   *
   * @return string
   *   The path to this repository.
   */
  public function getPath(): string;

  /**
   * Get the last commit hash on the current branch.
   *
   * This will return either the hash or throw an exception.
   *
   * @return string
   *   The last commit hash. This is a 40-character hexadecimal string.
   *
   * @throws \Drupal\va_gov_git\Exception\InvalidRepositoryException
   *   If the repository cannot be read.
   */
  public function getLastCommitHash(): string;

  /**
   * Get a list of remote branches.
   *
   * Note that these will include a prefix like `refs/remotes/origin`.
   *
   * The returned items will look like:
   * - `refs/remotes/origin/main`
   * - `refs/remotes/origin/revert-some-commit`
   *
   * @return array
   *   An array of branch names.
   */
  public function getRemoteBranchNames(): array;

}
