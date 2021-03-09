<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild\Command;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\advancedqueue\Entity\QueueInterface as AdvancedQueueInterface;
use Drupal\advancedqueue\Job;

/**
 * A service for working with the Web Build Command Queue.
 */
class Queue implements QueueInterface {

  /**
   * The queue storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $queueStorage;

  /**
   * The web build command job service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\CommandJobServiceInterface
   */
  protected $jobBuilder;

  /**
   * WebBuildCommandQueue constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\va_gov_build_trigger\FrontendBuild\Command\JobServiceInterface $jobBuilder
   *   Builds jobs from commands, extracts commands from jobs.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    JobServiceInterface $jobBuilder
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
  public function getQueue(): AdvancedQueueInterface {
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
