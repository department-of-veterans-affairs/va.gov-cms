<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild\Command;

use Drupal\advancedqueue\Job;
use Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType\WebBuildJobType;

/**
 * A service for working with web build command jobs.
 */
class JobService implements JobServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function getJob(array $commands = []): Job {
    $payload = [
      'commands' => $commands,
    ];
    return Job::create(WebBuildJobType::QUEUE_ID, $payload);
  }

  /**
   * {@inheritdoc}
   */
  public function getCommands(Job $job): array {
    $payload = $job->getPayload();
    return $payload['commands'] ?? [];
  }

}
