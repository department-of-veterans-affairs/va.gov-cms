<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild\Command;

/**
 * A service to build out commands array.
 */
interface BuilderInterface {

  /**
   * Build an array of commands to run for the web build.
   *
   * @param string|null $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   * @param string|null $unique_key
   *   A unique key to use in the branch name.  Defaults to time().
   *
   * @return array
   *   An array of commands to run for a build.
   */
  public function buildCommands(string $front_end_git_ref = NULL, string $unique_key = NULL) : array;

  /**
   * Build a composer command.
   *
   * @param string $composer_command
   *   The composer command to run.
   *
   * @return string
   *   The composer command line.
   */
  public function buildComposerCommand(string $composer_command) : string;

  /**
   * The name of the composer command to run.
   *
   * @param string|null $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   *
   * @return string
   *   The name of the composer command to run
   */
  public function commandName(string $front_end_git_ref = NULL) : string;

  /**
   * Build the command to check out a git reference.
   *
   * @param string $repo_root
   *   The path to the repository root.
   * @param string $unique_key
   *   A unique key to use in the branch name.
   * @param string|null $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   *
   * @return string
   *   The command to run to checkout the repo.
   */
  public function getFrontEndGitReferenceCheckoutCommand(string $repo_root, string $unique_key, string $front_end_git_ref = NULL) : string;

  /**
   * Build the commands to reinstall va-gov/web.
   *
   * @param string $repo_root
   *   The path to the repository root.
   *
   * @return array
   *   The commands to run to reinstall va-gov/web.
   */
  public function getFrontEndReinstallCommands(string $repo_root) : array;

  /**
   * Build the command to reset va-gov/web files to their default state.
   *
   * @param string $repo_root
   *   The path to the repository root.
   *
   * @return array
   *   The commands to run to reset va-gov/web files.
   */
  public function getFrontEndResetCommand(string $repo_root) : string;

}
