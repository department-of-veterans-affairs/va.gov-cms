<?php

namespace Drupal\va_gov_content_export\ExportCommand;

use Drupal\tome_sync\Event\TomeSyncEvents;
use Drupal\tome_sync\ExporterInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Build a list of commands to run for the export.
 */
class BuildCommands {
  use CommandRunner;

  /**
   * Tome Exporter.
   *
   * @var \Drupal\tome_sync\ExporterInterface
   */
  protected $exporter;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  private $eventDispatcher;

  /**
   * Execuitable Finder.
   *
   * @var \Drupal\va_gov_content_export\ExportCommand\ExecutableFinder
   */
  protected $executableFinder;

  /**
   * Set the tome export directory.
   *
   * @var string
   */
  protected $exportDir = '';

  /**
   * BuildCommands constructor.
   *
   * @param \Drupal\tome_sync\ExporterInterface $exporter
   *   The exporter.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   * @param \Drupal\va_gov_content_export\ExportCommand\ExecutableFinder $executableFinder
   *   Executable Finder.
   */
  public function __construct(ExporterInterface $exporter, EventDispatcherInterface $eventDispatcher, ExecutableFinder $executableFinder) {
    $this->exporter = $exporter;
    $this->eventDispatcher = $eventDispatcher;
    $this->executableFinder = $executableFinder;
  }

  /**
   * Build a list of commands to run.
   *
   * @param int $entity_count
   *   The number of entities to split commands by.
   *
   * @return array
   *   Array of commands.
   */
  public function buildCommands(int $entity_count) : array {
    $entities = $this->exporter->getContentToExport();
    $id_pairs = [];
    $commands = [];
    foreach ($entities as $entity_type_id => $ids) {
      foreach ($ids as $id) {
        $id_pairs[] = "$entity_type_id:$id";
      }
    }

    $executable = $this->executableFinder->findExecutable('va-gov-cms-export-all-content');
    foreach (array_chunk($id_pairs, $entity_count) as $chunk) {
      $cmd = $executable . ' va-gov-cms-export-content ' . escapeshellarg(implode(',', $chunk));
      if ($this->getExportDir()) {
        $cmd .= ' --export-dir=' . escapeshellarg($this->getExportDir());
      }
      $commands[] = $cmd;
    }

    return $commands;
  }

  /**
   * Run an array of commands.
   *
   * @param array $commands
   *   Array of Commands to run.
   * @param int $concurrency
   *   The number of processes to spawn.
   * @param int $retry_count
   *   The number of times to retry.
   *
   * @return array
   *   Array of errors.
   */
  public function run(array $commands, int $concurrency = 1, int $retry_count = 0) : array {
    $errors = $this->runCommands($commands, $concurrency, $retry_count);
    $this->eventDispatcher->dispatch(TomeSyncEvents::EXPORT_ALL, new Event());
    return $errors;
  }

  /**
   * Delete the export directory.
   *
   * @return bool
   *   Was the delete successful.
   */
  public function deleteExportDirectories() : bool {
    return $this->exporter->deleteExportDirectories();
  }

  /**
   * Get the export Directory.
   *
   * @return string
   *   The export directory.
   */
  public function getExportDir(): string {
    return $this->exportDir;
  }

  /**
   * Set the export directory.
   *
   * @param string $exportDir
   *   The directory to use for the export.
   */
  public function setExportDir(string $exportDir): void {
    $this->exportDir = $exportDir;
  }

}
