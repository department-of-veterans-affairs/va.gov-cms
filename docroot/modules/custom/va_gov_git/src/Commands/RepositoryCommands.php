<?php

namespace Drupal\va_gov_git\Commands;

use Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for interacting with the Repository Settings service.
 */
class RepositoryCommands extends DrushCommands {

  /**
   * The Repository Factory service.
   *
   * @var \Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface
   */
  protected $repositoryFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface $repositoryFactory
   *   The repository factory service.
   */
  public function __construct(RepositoryFactoryInterface $repositoryFactory) {
    $this->repositoryFactory = $repositoryFactory;
  }

  /**
   * Get the specified repository.
   *
   * @param string $repositoryName
   *   The name of the repository to get.
   *
   * @return \Drupal\va_gov_git\Repository\RepositoryInterface
   *   The repository.
   */
  public function getRepository(string $repositoryName) {
    return $this->repositoryFactory->get($repositoryName);
  }

  /**
   * Display the last commit hash for the current branch.
   *
   * @command va-gov-git:repository:get-last-commit-hash
   * @aliases va-gov-git-repository-get-last-commit-hash
   */
  public function getLastCommitHash(string $repositoryName) {
    $hash = $this->getRepository($repositoryName)->getLastCommitHash();
    $this->io()->writeln($hash);
  }

  /**
   * List remote branches.
   *
   * @command va-gov-git:repository:list-remote-branches
   * @aliases va-gov-git-repository-list-remote-branches
   */
  public function listRemoteBranches(string $repositoryName) {
    $branches = $this->getRepository($repositoryName)->getRemoteBranchNames();
    $this->io()->listing($branches);
  }

}
