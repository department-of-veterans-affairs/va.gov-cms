<?php

namespace Drupal\va_gov_git\Repository\Settings;

/**
 * Interface for the repository settings service.
 *
 * This service abstracts certain details of dealing with repository-specific
 * settings.
 */
interface RepositorySettingsInterface {

  // Repository names.
  const VA_GOV_CMS = 'va.gov-cms';
  const CONTENT_BUILD = 'content-build';
  const VETS_WEBSITE = 'vets-website';
  const NEXT_BUILD = 'next-build';
  const REPOSITORY_NAMES = [
    self::VA_GOV_CMS,
    self::CONTENT_BUILD,
    self::VETS_WEBSITE,
    self::NEXT_BUILD,
  ];

  // Settings keys for the repositories' filesystem paths.
  // The repositories are cloned in the course of normal operations, and these
  // should point to values matching those paths.
  const VA_GOV_CMS_PATH_KEY = 'va_gov_app_root';
  const CONTENT_BUILD_PATH_KEY = 'va_gov_web_root';
  const VETS_WEBSITE_PATH_KEY = 'va_gov_vets_website_root';
  const NEXT_BUILD_PATH_KEY = 'va_gov_next_build_root';

  const PATH_KEYS = [
    self::VA_GOV_CMS => self::VA_GOV_CMS_PATH_KEY,
    self::CONTENT_BUILD => self::CONTENT_BUILD_PATH_KEY,
    self::VETS_WEBSITE => self::VETS_WEBSITE_PATH_KEY,
    self::NEXT_BUILD => self::NEXT_BUILD_PATH_KEY,
  ];

  /**
   * Get the names for the repositories.
   *
   * @return array
   *   An array of repository names.
   */
  public function getNames(): array;

  /**
   * Get the path key for the given repository.
   *
   * @param string $name
   *   The name of the repository.
   *
   * @return string
   *   The path key for the given repository.
   *
   * @throws \Drupal\va_gov_git\Exception\UnknownRepositoryException
   *   If the given repository name is unknown.
   */
  public function getPathKey(string $name): string;

  /**
   * Get the path for the given repository.
   *
   * @param string $name
   *   The name of the repository.
   *
   * @return string
   *   The path for the given repository.
   *
   * @throws \Drupal\va_gov_git\Exception\InvalidRepositoryPathKeyException
   *   If the path key for the given repository is invalid.
   * @throws \Drupal\va_gov_git\Exception\RepositoryPathNotSetException
   *   If the path for the given repository is not set.
   * @throws \Drupal\va_gov_git\Exception\UnknownRepositoryException
   *   If the given repository name is unknown.
   */
  public function getPath(string $name): string;

  /**
   * List the available repository information.
   *
   * @return array
   *   An associative array with the following keys:
   *   - name: The name of the repository.
   *   - path: The path to the repository.
   */
  public function list(): array;

}
