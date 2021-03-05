<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\advancedqueue\Job;

/**
 * A service for managing the Web Build Command Queue.
 */
interface WebBuildCommandQueueInterface {

  /**
   * Enqueue a web build job.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   A web build job.
   */
  public function enqueueJob(Job $job): void;

  /**
   * Enqueue some commands as a web build job.
   *
   * @param array $commands
   *   Commands to enqueue as a job.
   */
  public function enqueueCommands(array $commands): void;

}
