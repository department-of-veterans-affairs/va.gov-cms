<?php

namespace Drupal\va_gov_build_trigger\Command;

use Symfony\Component\Process\Process;

/**
 * Shared code to run commands on the command line in an async manner.
 */
trait CommandRunner {

  /**
   * Runs commands with concurrency.
   *
   * @param string[] $commands
   *   An array of commands to execute.
   * @param int $concurrency
   *   The number of concurrent processes to execute.
   * @param int $retry_count
   *   The number of times to retry a failed command.
   * @param callable $callback
   *   (optional) A callback to invoke for each completed callback.
   *
   * @return array
   *   An array of errors encountered when running commands.
   */
  protected function runCommands(array $commands, $concurrency = 1, $retry_count = 0, $callback = NULL) : array {
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
