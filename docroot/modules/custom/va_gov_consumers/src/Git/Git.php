<?php

namespace Drupal\va_gov_consumers\Git;

/**
 * Interact with Git.
 *
 * Consider using https://github.com/gitonomy/gitlib if our needs expand.
 */
class Git implements GitInterface {

  /**
   * Path to repository Root.
   *
   * @var string
   */
  protected $repositoryRoot;

  /**
   * Git constructor.
   *
   * @param string $repositoryRoot
   *   The repository Root path.
   */
  public function __construct(string $repositoryRoot) {
    $this->repositoryRoot = $repositoryRoot;
  }

  /**
   * Factory method.
   *
   * @param string $repositoryRoot
   *   The repository root.
   *
   * @return \Drupal\va_gov_consumers\Git\Git
   *   The git class.
   */
  public static function get(string $repositoryRoot) : Git {
    return new static($repositoryRoot);
  }

  /**
   * {@inheritDoc}
   */
  public function searchBranches(string $search_string, int $count = 10) : array {
    // If we expand usage of looking at git, we need to not use shell_exec.
    $branches = explode(PHP_EOL, shell_exec("cd {$this->repositoryRoot} && git ls-remote --heads origin | cut -f2 | sed 's#refs/heads/##'"));
    $matches = array_filter($branches, static function ($branch_name) use ($search_string) {
      return stristr($branch_name, $search_string) !== FALSE;
    });

    return array_slice(array_values($matches), 0, $count);
  }

}
