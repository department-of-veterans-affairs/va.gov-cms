<?php

namespace Drupal\va_gov_git\Repository\Factory;

use Drupal\va_gov_git\Repository\Repository;
use Drupal\va_gov_git\Repository\RepositoryInterface;
use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;

/**
 * The Repository Factory service.
 *
 * This service provides a way to create services corresponding to specific Git
 * repositories.
 *
 * At this time, we're primarily interested in three repositories:
 * - The `va.gov-cms` repository.
 * - The `content-build` repository.
 * - The `vets-website` repository.
 */
class RepositoryFactory implements RepositoryFactoryInterface {

  /**
   * The repository settings service.
   *
   * @var \Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface
   */
  protected $repositorySettings;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface $repositorySettings
   *   The repository settings service.
   */
  public function __construct(RepositorySettingsInterface $repositorySettings) {
    $this->repositorySettings = $repositorySettings;
  }

  /**
   * {@inheritDoc}
   */
  public function get(string $name): RepositoryInterface {
    $path = $this->repositorySettings->getPath($name);
    return new Repository($name, $path);
  }

  /**
   * {@inheritDoc}
   */
  public function getCms(): RepositoryInterface {
    return $this->get(RepositorySettingsInterface::VA_GOV_CMS);
  }

  /**
   * {@inheritDoc}
   */
  public function getContentBuild(): RepositoryInterface {
    return $this->get(RepositorySettingsInterface::CONTENT_BUILD);
  }

  /**
   * {@inheritDoc}
   */
  public function getVetsWebsite(): RepositoryInterface {
    return $this->get(RepositorySettingsInterface::VETS_WEBSITE);
  }

}
