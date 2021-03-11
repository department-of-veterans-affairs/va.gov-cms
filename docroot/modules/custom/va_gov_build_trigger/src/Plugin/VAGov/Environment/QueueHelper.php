<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\advancedqueue\Entity\QueueInterface;
use Drupal\advancedqueue\Job;
use Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType\WebBuildJobType;

/**
 * A trait to help queueing of commands.
 *
 * Look into making this a service some day.
 */
trait QueueHelper {

  /**
   * Helper method to queue an array of commands.
   *
   * @param array $commands
   *   Array of commands to run.
   * @param \Drupal\advancedqueue\Entity\QueueInterface $queue
   *   The queue to run them against.
   */
  protected function queueCommands(array $commands, QueueInterface $queue) : void {
    $payload = ['commands' => $commands];

    $job = Job::create(WebBuildJobType::QUEUE_ID, $payload);
    $queue->enqueueJob($job);
  }

}
