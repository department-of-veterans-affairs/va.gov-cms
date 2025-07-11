<?php

namespace Drupal\va_gov_git\BranchSearch\Factory;

use Drupal\va_gov_git\BranchSearch\BranchSearchInterface;

/**
 * Interface for the branch search factory.
 *
 * This service provides a way to create branch search services corresponding
 * to specific Git repositories.
 *
 * At this time, we're primarily interested in three repositories:
 * - The `va.gov-cms` repository.
 * - The `content-build` repository.
 * - The `vets-website` repository.
 */
interface BranchSearchFactoryInterface {

  /**
   * Retrieve a specific repository branch search object, by name.
   *
   * @param string $name
   *   The name of the repository.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The branch search object.
   *
   * @throws \Drupal\va_gov_git\Exception\UnknownRepositoryException
   *   If the given repository name is unknown.
   */
  public function get(string $name): BranchSearchInterface;

  /**
   * Get the CMS repository branch search object.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The CMS branch search object.
   */
  public function getCms(): BranchSearchInterface;

  /**
   * Get the content-build branch search service.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The content-build branch search object.
   */
  public function getContentBuild(): BranchSearchInterface;

  /**
   * Get the vets-website branch search service.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The vets-website branch search object.
   */
  public function getVetsWebsite(): BranchSearchInterface;

  /**
   * Get the next-build branch search service.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The next-build branch search object.
   */
  public function getNextBuild(): BranchSearchInterface;

  /**
   * Get the next-build-vets-website branch search service.
   *
   * @return \Drupal\va_gov_git\BranchSearch\BranchSearchInterface
   *   The next-build-vets-website branch search object.
   */
  public function getNextBuildVetsWebsite(): BranchSearchInterface;

}
