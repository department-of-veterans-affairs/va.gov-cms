<?php

namespace Drupal\va_gov_git\Commands;

use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for interacting with the Repository Settings service.
 */
class RepositorySettingsCommands extends DrushCommands {

  /**
   * The Repository Settings service.
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
   * Display the current repositories.
   *
   * @command va-gov-git:repository-settings:get-names
   * @aliases va-gov-git-repository-settings-get-names
   */
  public function getNames() {
    $this->io()->listing($this->repositorySettings->getNames());
  }

  /**
   * Get the path key for the given repository.
   *
   * @command va-gov-git:repository-settings:get-path-key
   * @aliases va-gov-git-repository-settings-get-path-key
   */
  public function getPathKey(string $name) {
    $this->io()->writeln($this->repositorySettings->getPathKey($name));
  }

  /**
   * Get the path for the given repository.
   *
   * @command va-gov-git:repository-settings:get-path
   * @aliases va-gov-git-repository-settings-get-path
   */
  public function getPath(string $name) {
    $this->io()->writeln($this->repositorySettings->getPath($name));
  }

  /**
   * List the available repositories and their corresponding paths.
   *
   * @command va-gov-git:repository-settings:list
   * @aliases va-gov-git-repository-settings-list
   */
  public function list() {
    $this->io()->table(
      ['Name', 'Path'],
      $this->repositorySettings->list()
    );
  }

}
