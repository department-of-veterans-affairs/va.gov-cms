<?php

namespace Drupal\va_gov_git\Commands;

use Drupal\va_gov_git\BranchSearch\Factory\BranchSearchFactoryInterface;
use Drupal\va_gov_git\BranchSearch\BranchSearchInterface;
use Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface;
use Drupal\va_gov_git\Repository\RepositoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for interacting with the Repository Settings service.
 */
class RepositoryCommands extends DrushCommands {

  /**
   * The Branch Search Factory service.
   *
   * @var \Drupal\va_gov_git\BranchSearch\Factory\BranchSearchFactoryInterface
   */
  protected $branchSearchFactory;

  /**
   * The Repository Factory service.
   *
   * @var \Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface
   */
  protected $repositoryFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_git\BranchSearch\Factory\BranchSearchFactoryInterface $branchSearchFactory
   *   The branch search factory service.
   * @param \Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface $repositoryFactory
   *   The repository factory service.
   */
  public function __construct(
    BranchSearchFactoryInterface $branchSearchFactory,
    RepositoryFactoryInterface $repositoryFactory
  ) {
    $this->branchSearchFactory = $branchSearchFactory;
    $this->repositoryFactory = $repositoryFactory;
  }

  /**
   * Get the specified branch service object.
   *
   * @param string $repositoryName
   *   The name of the repository to get.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The branch service.
   */
  public function getBranchSearch(string $repositoryName): BranchSearchInterface {
    return $this->branchSearchFactory->get($repositoryName);
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
  public function getRepository(string $repositoryName): RepositoryInterface {
    return $this->repositoryFactory->get($repositoryName);
  }

  /**
   * Display the last commit hash for the current branch.
   *
   * @param string $repositoryName
   *   The name of the repository to get.
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
   * @param string $repositoryName
   *   The name of the repository to get.
   * @param string $remote
   *   The name of the remote.
   *
   * @command va-gov-git:repository:list-remote-branches
   * @aliases va-gov-git-repository-list-remote-branches
   */
  public function listRemoteBranches(string $repositoryName, string $remote = 'origin') {
    $branchSearch = $this->getBranchSearch($repositoryName);
    $branches = $branchSearch->getRemoteBranchNames($remote);
    $this->io()->listing($branches);
  }

  /**
   * List remote branches containing the specified string.
   *
   * @param string $repositoryName
   *   The name of the repository to get.
   * @param string $string
   *   The string to search for.
   * @param string $remote
   *   The name of the remote.
   *
   * @command va-gov-git:repository:search-remote-branches
   * @aliases va-gov-git-repository-search-remote-branches
   */
  public function searchRemoteBranches(string $repositoryName, string $string, string $remote = 'origin') {
    $branchSearch = $this->getBranchSearch($repositoryName);
    $branches = $branchSearch->getRemoteBranchNamesContaining($string, $remote);
    $this->io()->listing($branches);
  }

}
