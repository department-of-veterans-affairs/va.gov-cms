<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\advancedqueue\Job;

/**
 * A service for working with web build command jobs (whew!).
 */
interface WebBuildCommandJobServiceInterface {

  /**
   * Convert an array of commands into a job.
   *
   * @param array $commands
   *   A list of commands to execute from a shell.
   *
   * @return \Drupal\advancedqueue\Job
   *   An AdvancedQueue job with a payload of commands.
   */
  public function getJob(array $commands = []): Job;

  /**
   * Convert a job into a list of commands.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   An AdvancedQueue job with a payload of commands.
   *
   * @return array
   *   A list of commands to execute from a shell.
   */
  public function getCommands(Job $job): array;

}
