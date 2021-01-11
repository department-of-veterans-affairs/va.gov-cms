<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\Core\Site\Settings;

/**
 * A service to build out commands array.
 */
class WebBuildCommandBuilder {

  public const USE_CMS_EXPORT_SETTING = 'va_gov_use_cms_export';

  /**
   * App Root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * Use CMS Export.
   *
   * @var bool
   */
  protected $useCMSExport;

  /**
   * WebBuildCommandBuilder constructor.
   *
   * @param string $appRoot
   *   Drupal App Root.
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   */
  public function __construct(string $appRoot, Settings $settings) {
    $this->appRoot = $appRoot;
    $this->useCMSExport = $settings->get(static::USE_CMS_EXPORT_SETTING, FALSE);
  }

  /**
   * Build an array of commands to run for the web build.
   *
   * @param string $base_path
   *   The path to the repository root.
   * @param string $composer_home
   *   THe composer home.
   * @param string $composer_path
   *   The path to composer.
   * @param string|null $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   *
   * @return array
   *   An array of commands to run for a build.
   */
  public function buildCommands(string $base_path, string $composer_home, string $composer_path, string $front_end_git_ref = NULL) : array {
    $commands = [];

    $composer_command = $this->commandName();
    if ($command = $this->getFrontEndGitReferenceCheckoutCommand(time(), $front_end_git_ref)) {
      $commands[] = $command;
      $commands[] = $this->buildComposerCommand($base_path, $composer_home, $composer_path, 'va:web:install');
    }

    $commands[] = $this->buildComposerCommand($base_path, $composer_home, $composer_path, $composer_command);

    return $commands;
  }

  /**
   * Build a composer command.
   *
   * @param string $base_path
   *   The path to the repository root.
   * @param string $composer_home
   *   THe composer home.
   * @param string $composer_path
   *   The path to composer.
   * @param string $composer_command
   *   The composer command to run.
   *
   * @return string
   *   The composer command line.
   */
  protected function buildComposerCommand(string $base_path, string $composer_home, string $composer_path, string $composer_command) : string {
    return "cd $base_path && COMPOSER_HOME=$composer_home $composer_path --no-cache $composer_command";
  }

  /**
   * The name of the composer command to run.
   *
   * @param string|null $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   *
   * @return string
   *   The name of the composer command to run
   */
  protected function commandName(string $front_end_git_ref = NULL) : string {
    $command = 'va:web:build';

    if ($this->useCMSExport) {
      $command .= ':export';
    }

    if ($front_end_git_ref) {
      $command .= ':full';
    }

    return $command;
  }

  /**
   * {@inheritDoc}
   */
  protected function getFrontEndGitReferenceCheckoutCommand(string $build_date, string $front_end_git_ref = NULL) : string {
    $web_branch = "build-{$front_end_git_ref}-{$build_date}";

    if (is_numeric($front_end_git_ref)) {
      return "cd {$this->appRoot}/web && git fetch origin pull/{$front_end_git_ref}/head:{$web_branch} && git checkout {$web_branch}";
    }

    if ($front_end_git_ref) {
      return "cd {$this->appRoot}/web && git checkout -b {$web_branch} origin/{$front_end_git_ref}";
    }

    return '';
  }

}
