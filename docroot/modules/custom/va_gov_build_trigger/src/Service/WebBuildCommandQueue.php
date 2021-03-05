<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\advancedqueue\Entity\QueueInterface;
use Drupal\advancedqueue\Job;

/**
 * A service for working with the Web Build Command Queue.
 */
class WebBuildCommandQueue implements WebBuildCommandQueueInterface {

  /**
   * The queue storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $queueStorage;

  /**
   * The web build command job service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\WebBuildCommandJobServiceInterface
   */
  protected $jobBuilder;

  /**
   * WebBuildCommandQueue constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\va_gov_build_trigger\WebBuildCommandJobServiceInterface $jobBuilder
   *   Builds jobs from commands, extracts commands from jobs.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    WebBuildCommandJobServiceInterface $jobBuilder
  ) {
    $this->queueStorage = $entityTypeManager->getStorage('advancedqueue_queue');
    $this->jobBuilder = $jobBuilder;
  }

  /**
   * Retrieve the queue.
   *
   * @return \Drupal\advancedqueue\Entity\QueueInterface
   *   The queue.
   */
  public function getQueue(): QueueInterface {
    return $this->queueStorage->load('command_runner');
  }

  /**
   * {@inheritdoc}
   */
  public function enqueueJob(Job $job): void {
    $this->getQueue()->enqueueJob($job);
  }

  /**
   * {@inheritdoc}
   */
  public function enqueueCommands(array $commands): void {
    $this->enqueueJob($this->jobBuilder->getJob($commands));
  }

}
