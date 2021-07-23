<?php

namespace Drupal\va_gov_consumers\Git;

use Gitonomy\Git\Commit;
use Gitonomy\Git\Repository;

/**
 * Interact with Git.
 *
 * Consider using https://github.com/gitonomy/gitlib if our needs expand.
 */
class Git implements GitInterface {

  /**
   * Repository class.
   *
   * @var \Gitonomy\Git\Repository
   */
  protected $repository;

  /**
   * Git constructor.
   *
   * @param \Gitonomy\Git\Repository $repository
   *   The repository class.
   */
  public function __construct(Repository $repository) {
    $this->repository = $repository;
  }

  /**
   * Factory method.
   *
   * @param \Gitonomy\Git\Repository $repository
   *   The Repository Class.
   *
   * @return \Drupal\va_gov_consumers\Git\Git
   *   The git class.
   */
  public static function get(Repository $repository) : Git {
    return new static($repository);
  }

  /**
   * {@inheritDoc}
   */
  public function searchBranches(string $search_string, int $count = 10) : array {
    $repository = $this->repository->getPath();
    // If we expand usage of looking at git, we need to not use shell_exec.
    $branches = explode(PHP_EOL, shell_exec("cd {$repository} && git ls-remote --heads origin | cut -f2 | sed 's#refs/heads/##'"));
    $matches = array_filter($branches, static function ($branch_name) use ($search_string) {
      return stristr($branch_name, $search_string) !== FALSE;
    });

    return array_slice(array_values($matches), 0, $count);
  }

  /**
   * {@inheritDoc}
   */
  public function getLastCommit(): ?Commit {
    return $this->repository->getHeadCommit();
  }

}
