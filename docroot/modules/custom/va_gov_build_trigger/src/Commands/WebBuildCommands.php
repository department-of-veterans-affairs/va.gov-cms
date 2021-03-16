<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\ProcessorInterface as QueueProcessorInterface;
use Drupal\va_gov_build_trigger\Service\BuildFrontendInterface;
use Drupal\va_gov_build_trigger\CommandExportable;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush interface to the Frontend Build dispatcher service.
 */
class WebBuildCommands extends DrushCommands {

  use CommandExportable;

  /**
   * The command builder service.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface
   */
  protected $commandBuilder;

  /**
   * The frontend build service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildFrontendInterface
   */
  protected $buildService;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The queue processor.
   *
   * @var \Drupal\advancedqueue\ProcessorInterface
   */
  protected $queueProcessor;

  /**
   * The web build command queue.
   *
   * @var \Drupal\advancedQueue\Entity\QueueInterface
   */
  protected $queue;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface $commandBuilder
   *   The web build command builder.
   * @param \Drupal\va_gov_build_trigger\Service\BuildFrontendInterface $buildService
   *   The frontend build dispatcher service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\advancedqueue\ProcessorInterface $queueProcessor
   *   The AdvancedQueue queue processor.
   */
  public function __construct(
    WebBuildCommandBuilderInterface $commandBuilder,
    BuildFrontendInterface $buildService,
    Connection $database,
    EntityTypeManagerInterface $entityTypeManager,
    QueueProcessorInterface $queueProcessor
  ) {
    $this->commandBuilder = $commandBuilder;
    $this->buildService = $buildService;
    $this->database = $database;
    $this->queue = $entityTypeManager
      ->getStorage('advancedqueue_queue')
      ->load('command_runner');
    $this->queueProcessor = $queueProcessor;
  }

  /**
   * {@inheritDoc}
   */
  protected function getWebBuildCommandBuilder(): WebBuildCommandBuilderInterface {
    return $this->commandBuilder;
  }

  /**
   * Dispatch a frontend build.
   *
   * @param string|null $reference
   *   A git reference, or null.
   * @param string $fullRebuild
   *   Will be coerced to a boolean.
   * @param array $options
   *   Command-line options.
   *
   * @command va-gov:build-frontend
   * @aliases va-gov-build-frontend
   * @option dry-run
   *   Don't actually build; just print the commands that would be executed.
   */
  public function buildFrontend(
    string $reference = NULL,
    string $fullRebuild = 'FALSE',
    array $options = [
      'dry-run' => FALSE,
    ]
  ) {
    if (filter_var($reference, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== NULL) {
      $fullRebuild = $reference;
      $reference = NULL;
    }
    if (empty($reference)) {
      $reference = NULL;
    }
    $fullRebuild = filter_var($fullRebuild, FILTER_VALIDATE_BOOLEAN);
    if ($options['dry-run']) {
      $buildCommands = [];

      $newCommands = $this->getWebBuildCommandBuilder()->buildCommands($reference, $fullRebuild);
      $buildCommands = array_merge($buildCommands, $newCommands);
      foreach ($buildCommands as $buildCommand) {
        echo $buildCommand . PHP_EOL;
      }
    }
    else {
      $this->buildService->triggerFrontendBuild($reference, $fullRebuild);
    }
  }

  /**
   * Process jobs in the web build queue.
   *
   * @command va-gov:build-frontend:process-queue
   * @aliases va-gov-build-frontend-process-queue
   */
  public function processQueue() {
    $this->queueProcessor->processQueue($this->queue);
  }

  /**
   * Empty the web build queue.
   *
   * This will remove all jobs from the queue, even if they haven't been
   * enqueued.
   *
   * @command va-gov:build-frontend:empty-queue
   * @aliases va-gov-build-frontend-empty-queue
   */
  public function emptyQueue() {
    $this->database->delete('advancedqueue')
      ->condition('queue_id', 'command_runner')
      ->execute();
  }

  /**
   * Delete all jobs in the web build queue.
   *
   * @command va-gov:build-frontend:delete-jobs
   * @aliases va-gov-build-frontend-delete-jobs
   */
  public function deleteQueueJobs() {
    $backend = $this->queue->getBackend();
    foreach ($this->getJobs() as $job) {
      $backend->deleteJob($job->getId());
    }
  }

  /**
   * Release all jobs in the web build queue.
   *
   * @command va-gov:build-frontend:release-jobs
   * @aliases va-gov-build-frontend-release-jobs
   */
  public function releaseQueueJobs() {
    $backend = $this->queue->getBackend();
    foreach ($this->getJobs() as $job) {
      $backend->releaseJob($job->getId());
    }
  }

  /**
   * List jobs in the web build queue.
   *
   * @command va-gov:build-frontend:list-jobs
   * @aliases va-gov-build-frontend-list-jobs
   */
  public function listQueueJobs() {
    $rows = [];
    foreach ($this->getJobs() as $job) {
      $rows[] = $job->toArray();
    }
    return new RowsOfFields($rows);
  }

  /**
   * Get content release build jobs.
   *
   * @return array[\Drupal\advancedqueue\Job]
   *   Array of Jobs.
   */
  public function getJobs() : array {
    $jobs = [];
    $result = $this->database->select('advancedqueue', 'aq')
      ->condition('aq.queue_id', 'command_runner')
      ->fields('aq')
      ->range(0, 1000)
      ->orderBy('available', 'DESC')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $jobs[] = new Job($record);
    }
    return $jobs;
  }

}
