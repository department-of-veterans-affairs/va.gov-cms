<?php

namespace Drupal\va_gov_content_export\ExportCommand;

use Drupal\tome_sync\Event\TomeSyncEvents;
use Drupal\tome_sync\ExporterInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

/**
 * Build a list of commands to run for the export.
 */
class BuildCommands {

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
      $commands[] = $executable . ' va-gov-cms-export-content ' . escapeshellarg(implode(',', $chunk));
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
   * Runs commands with concurrency.
   *
   * @param string[] $commands
   *   An array of commands to execute.
   * @param int $concurrency
   *   The number of concurrent processes to execute.
   * @param int $retry_count
   *   The number of times to retry a failed command.
   * @param callback|\Closure $callback
   *   (optional) A callback to invoke for each completed callback.
   *
   * @see \Drupal\tome_base\ProcessTrait::runCommands()
   *
   * @return array
   *   An array of errors encountered when running commands.
   */
  protected function runCommands(array $commands, $concurrency, $retry_count, $callback = NULL): array {
    $current_processes = [];
    $collected_errors = [];

    $retry_callback = static function (&$current_process) use (&$collected_errors, $retry_count) {
      /** @var \Symfony\Component\Process\Process $process */
      $process = $current_process['process'];
      $command = $process->getCommandLine();
      if (!$process->isRunning() && !$process->isSuccessful() && $current_process['retry'] < $retry_count) {
        $collected_errors[] = "Retrying \"{$command}\" after failure...";
        $current_process['process'] = $process->restart();
        ++$current_process['retry'];
      }
    };

    $filter_callback = static function ($current_process) use (&$collected_errors, $callback) {
      /** @var \Symfony\Component\Process\Process $process */
      $process = $current_process['process'];
      $is_running = $process->isRunning();
      $command = $process->getCommandLine();
      if (!$is_running) {
        if (!$process->isSuccessful()) {
          $error_output = $process->getErrorOutput();
          $collected_errors[] = "Error when running \"{$command}\":\n  $error_output";
        }
        if ($callback) {
          call_user_func($callback, $current_process['process']);
        }
      }
      return $is_running;
    };

    while ($commands || $current_processes) {
      array_walk($current_processes, $retry_callback);
      $current_processes = array_filter($current_processes, $filter_callback);
      if ($commands && count($current_processes) < $concurrency) {
        $command = array_shift($commands);
        $process = new Process($command, $_SERVER['PWD'] ?? NULL);
        $process->start();
        $current_processes[] = [
          'process' => $process,
          'retry' => 0,
        ];
      }
      usleep(50000);
    }

    return $collected_errors;
  }

}
