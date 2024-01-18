<?php

namespace Drupal\va_gov_git\Repository\Factory;

use Drupal\va_gov_git\Repository\RepositoryInterface;

/**
 * Interface for the repository factory.
 *
 * This service provides a way to create services corresponding to specific Git
 * repositories.
 *
 * At this time, we're primarily interested in four repositories:
 * - The `va.gov-cms` repository.
 * - The `content-build` repository.
 * - The `vets-website` repository.
 * - The `next-build` repository.
 */
interface RepositoryFactoryInterface {

  /**
   * Retrieve a specific repository, by name.
   *
   * @param string $name
   *   The name of the repository.
   *
   * @return \Drupal\va_gov_git\Repository\RepositoryInterface
   *   The repository.
   *
   * @throws \Drupal\va_gov_git\Exception\UnknownRepositoryException
   *   If the given repository name is unknown.
   */
  public function get(string $name): RepositoryInterface;

  /**
   * Get the CMS repository.
   *
   * @return \Drupal\va_gov_git\Repository\RepositoryInterface
   *   The CMS repository.
   */
  public function getCms(): RepositoryInterface;

  /**
   * Get the content-build repository.
   *
   * @return \Drupal\va_gov_git\Repository\RepositoryInterface
   *   The content-build repository.
   */
  public function getContentBuild(): RepositoryInterface;

  /**
   * Get the vets-website repository.
   *
   * @return \Drupal\va_gov_git\Repository\RepositoryInterface
   *   The vets-website repository.
   */
  public function getVetsWebsite(): RepositoryInterface;

  /**
   * Get the next-build repository.
   *
   * @return \Drupal\va_gov_git\Repository\RepositoryInterface
   *   The next-build repository.
   */
  public function getNextBuild(): RepositoryInterface;

}
