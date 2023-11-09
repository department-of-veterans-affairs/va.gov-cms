<?php

namespace Drupal\va_gov_git\BranchSearch\Factory;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\va_gov_git\BranchSearch\BranchSearch;
use Drupal\va_gov_git\BranchSearch\BranchSearchInterface;
use Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface;

/**
 * The branch search service factory.
 *
 * This service provides a way to create branch search services corresponding
 * to specific Git repositories.
 *
 * At this time, we're primarily interested in three repositories:
 * - The `va.gov-cms` repository.
 * - The `content-build` repository.
 * - The `vets-website` repository.
 */
class BranchSearchFactory implements BranchSearchFactoryInterface {

  /**
   * The repository factory service.
   *
   * @var \Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface
   */
  protected $repositoryFactory;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   *   The logger factory.
   */
  protected $loggerFactory;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_git\Repository\Factory\RepositoryFactoryInterface $repositoryFactory
   *   The repository factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory.
   */
  public function __construct(
    RepositoryFactoryInterface $repositoryFactory,
    LoggerChannelFactoryInterface $loggerFactory
  ) {
    $this->repositoryFactory = $repositoryFactory;
    $this->loggerFactory = $loggerFactory;
    $this->logger = $loggerFactory->get('va_gov_git');
  }

  /**
   * {@inheritDoc}
   */
  public function get(string $name): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->get($name), $this->loggerFactory);
  }

  /**
   * {@inheritDoc}
   */
  public function getCms(): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->getCms(), $this->loggerFactory);
  }

  /**
   * {@inheritDoc}
   */
  public function getContentBuild(): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->getContentBuild(), $this->loggerFactory);
  }

  /**
   * {@inheritDoc}
   */
  public function getVetsWebsite(): BranchSearchInterface {
    return new BranchSearch($this->repositoryFactory->getVetsWebsite(), $this->loggerFactory);
  }

}
