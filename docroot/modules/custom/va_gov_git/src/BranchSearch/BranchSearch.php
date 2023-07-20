<?php

namespace Drupal\va_gov_git\BranchSearch;

use Drupal\va_gov_git\Repository\RepositoryInterface;

/**
 * The branch search service.
 */
class BranchSearch implements BranchSearchInterface {

  /**
   * The repository.
   *
   * @var \Drupal\va_gov_git\Repository\RepositoryInterface
   */
  protected $repository;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_git\Repository\RepositoryInterface $repository
   *   The repository.
   */
  public function __construct(RepositoryInterface $repository) {
    $this->repository = $repository;
  }

  /**
   * Return front end branch names matching the given string.
   *
   * @param string $string
   *   Search string.
   *
   * @return string[]
   *   Array of branch names.
   */
  public function searchFrontEndBranches(string $string) : array {
    try {
      $branches = $this->contentBuildRepository->getRemoteBranchNamesContaining($string);
      return $branches;
    }
    catch (\Throwable $exception) {
      $this->logger->error('Error searching for branches: @message', [
        '@message' => $exception->getMessage(),
      ]);
      return [];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getRemoteBranchNames(string $remote = 'origin'): array {
    $branches = $this->repository->getRemoteBranchNames();
    $branchPrefix = "refs/remotes/${remote}/";
    $branches = array_filter($branches, function ($branch) use ($branchPrefix) {
      return str_starts_with($branch, $branchPrefix);
    });
    $branches = array_map(function ($branch) use ($branchPrefix) {
      return str_replace($branchPrefix, '', $branch);
    }, $branches);
    return array_values($branches);
  }

  /**
   * {@inheritDoc}
   */
  public function getRemoteBranchNamesContaining(string $string, string $remote = 'origin'): array {
    $branches = $this->getRemoteBranchNames($remote);
    $branches = array_filter($branches, function ($branchName) use ($string) {
      return str_contains($branchName, $string);
    });
    return array_values($branches);
  }

}
