<?php

namespace Drupal\va_gov_git\Repository;

use Drupal\va_gov_git\Exception\InvalidRepositoryException;
use Gitonomy\Git\Repository as InnerRepository;

/**
 * Class for repository objects.
 *
 * This class wraps a repository object, likely vended by some third-party
 * code.
 */
class Repository implements RepositoryInterface {

  /**
   * The name of this repository.
   *
   * @var string
   */
  protected $name;

  /**
   * The path to this repository.
   *
   * @var string
   */
  protected $path;

  /**
   * The inner repository object.
   *
   * @var \Gitonomy\Git\Repository
   */
  protected $innerRepository;

  /**
   * Constructor.
   *
   * @param string $name
   *   The name of this repository.
   * @param string $path
   *   The path to this repository.
   */
  public function __construct(string $name, string $path) {
    $this->name = $name;
    $this->path = $path;
    $this->innerRepository = new InnerRepository($path);
  }

  /**
   * {@inheritDoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritDoc}
   */
  public function getPath(): string {
    return $this->path;
  }

  /**
   * {@inheritDoc}
   */
  public function getLastCommitHash(): string {
    try {
      $headCommit = $this->innerRepository->getHeadCommit();
      if ($headCommit === NULL) {
        throw new InvalidRepositoryException('No HEAD commit found in repository: ' . $this->name);
      }
      return $headCommit->getHash();
    }
    catch (\Throwable $exception) {
      throw new InvalidRepositoryException('Unable to read repository: ' . $this->name, $exception->getCode(), $exception);
    }
  }

  /**
   * Return the references.
   *
   * @return \Gitonomy\Git\Reference[]
   *   The references.
   */
  public function getReferences(): array {
    return $this->innerRepository->getReferences();
  }

  /**
   * {@inheritDoc}
   */
  public function getRemoteBranchNames(): array {
    $branches = $this->innerRepository->getReferences()->getRemoteBranches();
    return array_map(function ($reference) {
      return $reference->getFullname();
    }, $branches);
  }

}
