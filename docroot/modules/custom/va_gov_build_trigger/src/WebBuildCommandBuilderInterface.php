<?php

namespace Drupal\va_gov_build_trigger;

/**
 * A service to build out commands array.
 */
interface WebBuildCommandBuilderInterface {

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

}
