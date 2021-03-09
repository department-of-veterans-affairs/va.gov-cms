<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild\Command;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface;

/**
 * A service to build out commands array.
 */
class Builder implements BuilderInterface {

  public const COMPOSER_HOME = 'va_gov_composer_home';
  public const PATH_TO_COMPOSER = 'va_gov_path_to_composer';
  public const APP_ROOT = 'va_gov_app_root';
  public const WEB_ROOT = 'va_gov_web_root';

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
  protected $useContentExport;

  /**
   * Composer Home Directory.
   *
   * @var string
   */
  protected $composerHome;

  /**
   * Path to composer executable.
   *
   * @var string
   */
  protected $pathToComposer;

  /**
   * Path to Web root.
   *
   * @var string
   */
  protected $pathToWebRoot;

  /**
   * WebBuildCommandBuilder constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   * @param \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface $webBuildStatus
   *   WebBuild status.
   */
  public function __construct(Settings $settings, StatusInterface $webBuildStatus) {
    $this->useContentExport = $webBuildStatus->useContentExport();
    $this->appRoot = $settings->get(static::APP_ROOT, '');
    $this->composerHome = $settings->get(static::COMPOSER_HOME, '');
    $this->pathToComposer = $settings->get(static::PATH_TO_COMPOSER, '');
    $this->pathToWebRoot = $settings->get(static::WEB_ROOT, '');
  }

  /**
   * {@inheritdoc}
   */
  public function buildCommands(string $front_end_git_ref = NULL, string $unique_key = NULL) : array {
    $commands = [];

    $unique_key = $unique_key ?? (string) time();
    $repo_root = $this->getPathToWebRoot();

    if (!$front_end_git_ref) {
      // If no git reference is passed, reset va-gov/web to the default tag. We
      // do this to ensure that the default tag is used even if a branch or PR
      // was checked out earlier.
      $commands += $this->getFrontEndReinstallCommands($repo_root);
    }
    else {
      // If we are checking out a branch or PR, reset all files in va-gov/web
      // to their default state. We do this to avoid having the checkout fail
      // if there are modified files.
      $commands[] = $this->getFrontEndResetCommand($repo_root);
    }

    $composer_command = $this->commandName($front_end_git_ref);
    if ($command = $this->getFrontEndGitReferenceCheckoutCommand($repo_root, $unique_key, $front_end_git_ref)) {
      $commands[] = $command;
      $commands[] = $this->buildComposerCommand('va:web:install');
    }

    $commands[] = $this->buildComposerCommand($composer_command);

    return $commands;
  }

  /**
   * {@inheritdoc}
   */
  public function buildComposerCommand(string $composer_command) : string {
    return "cd {$this->appRoot} && COMPOSER_HOME={$this->composerHome} {$this->pathToComposer} --no-cache $composer_command";
  }

  /**
   * {@inheritdoc}
   */
  public function commandName(string $front_end_git_ref = NULL) : string {
    $command = 'va:web:build';

    if ($this->useContentExport()) {
      $command .= ':export';
    }

    if ($front_end_git_ref) {
      $command .= ':full';
    }

    return $command;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrontEndGitReferenceCheckoutCommand(string $repo_root, string $unique_key, string $front_end_git_ref = NULL) : string {
    $web_branch = "build-{$front_end_git_ref}-{$unique_key}";

    if (is_numeric($front_end_git_ref)) {
      return "cd {$repo_root} && git fetch origin pull/{$front_end_git_ref}/head:{$web_branch} && git checkout {$web_branch}";
    }

    if ($front_end_git_ref) {
      return "cd {$repo_root} && git fetch origin && git checkout -b {$web_branch} origin/{$front_end_git_ref}";
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFrontEndReinstallCommands(string $repo_root) : array {
    $commands = ["cd {$repo_root} && rm -fr docroot/vendor/va-gov"];
    $commands[] = $this->buildComposerCommand('install');

    return $commands;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrontEndResetCommand(string $repo_root) : string {
    return "cd {$repo_root} && git reset --hard HEAD";
  }

  /**
   * Use CMS export.
   *
   * @return bool
   *   Should we use cms export?
   */
  public function useContentExport() : bool {
    return $this->useContentExport;
  }

  /**
   * Path to App Root.
   *
   * @return string
   *   Path to App Root.
   */
  public function getAppRoot(): string {
    return $this->appRoot;
  }

  /**
   * Path to Composer Home.
   *
   * @return string
   *   Path to composer home.
   */
  public function getComposerHome(): string {
    return $this->composerHome;
  }

  /**
   * Path to composer executable.
   *
   * @return string
   *   Path to composer executable.
   */
  public function getPathToComposer(): string {
    return $this->pathToComposer;
  }

  /**
   * Path to Web Root.
   *
   * @return string
   *   Path to web root.
   */
  public function getPathToWebRoot(): string {
    return $this->pathToWebRoot;
  }

}
