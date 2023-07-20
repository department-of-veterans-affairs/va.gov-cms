<?php

namespace Drupal\va_gov_git\Repository\Settings;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_git\Exception\InvalidRepositoryPathKeyException;
use Drupal\va_gov_git\Exception\RepositoryPathNotSetException;
use Drupal\va_gov_git\Exception\UnknownRepositoryException;

/**
 * The repository settings service.
 *
 * This service abstracts certain details of dealing with repository-specific
 * settings.
 */
class RepositorySettings implements RepositorySettingsInterface {

  /**
   * The Drupal Settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal Settings service.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function getNames(): array {
    return RepositorySettingsInterface::REPOSITORY_NAMES;
  }

  /**
   * {@inheritDoc}
   */
  public function getPathKey(string $name): string {
    if (!in_array($name, $this->getNames())) {
      throw new UnknownRepositoryException('Unknown repository: ' . $name);
    }
    return RepositorySettingsInterface::PATH_KEYS[$name];
  }

  /**
   * {@inheritDoc}
   */
  public function getPath(string $name): string {
    $pathKey = $this->getPathKey($name);
    if (empty($pathKey)) {
      throw new InvalidRepositoryPathKeyException('Invalid path key for repository: ' . $name);
    }
    $path = $this->settings->get($pathKey);
    if (empty($path)) {
      throw new RepositoryPathNotSetException('Path not set for repository: ' . $name);
    }
    return $path;
  }

  /**
   * {@inheritDoc}
   */
  public function list(): array {
    return array_map(function ($name) {
      return [
        'name' => $name,
        'path' => $this->getPath($name),
      ];
    }, $this->getNames());
  }

}
