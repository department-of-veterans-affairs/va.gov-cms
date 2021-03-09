<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild\Command;

use Drupal\advancedqueue\Job;

/**
 * A service for managing the Frontend Build Command Queue.
 */
interface QueueInterface {

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
