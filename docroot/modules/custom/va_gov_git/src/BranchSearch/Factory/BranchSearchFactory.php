<?php

namespace Drupal\va_gov_git\BranchSearch\Factory;

use Drupal\va_gov_git\BranchSearch\BranchSearch;
use Drupal\va_gov_git\BranchSearch\BranchSearchInterface;
use Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface;

/**
 * The branch search service factory.
 *
 * This service provides a way to create branch search services corresponding
 * to specific Git repositories.
 *
 * At this time, we're primarily interested in two repositories:
 * - The `va.gov-cms` repository.
 * - The `content-build` repository.
 */
class BranchSearchFactory implements BranchSearchFactoryInterface {

  /**
   * The repository factory service.
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
   * {@inheritDoc}
   */
  public function get(string $name): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->get($name));
  }

  /**
   * {@inheritDoc}
   */
  public function getCms(): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->getCms());
  }

  /**
   * {@inheritDoc}
   */
  public function getContentBuild(): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->getContentBuild());
  }

}
