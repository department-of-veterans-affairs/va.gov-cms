<?php

namespace Tests\Support\Traits;

/**
 * Allows for logging to a file to ease debugging in non-interactive settings.
 *
 * This trait is meant to be used only by test classes.
 */
trait FileLoggerTrait {

  /**
   * Retrieves the log file path.
   *
   * @return string
   *   An absolute path to the log file.
   */
  public function getLogFilePath(): string {
    $rootPath = getenv('DDEV_APPROOT') ?: getenv('TUGBOAT_ROOT') ?: getenv('RUNNER_TEMP') ?: '/var/www/cms';
    return "$rootPath/phpunit.log";
  }

  /**
   * Write a message to the log file.
   *
   * @param string $message
   *   The message to write.
   */
  public function writeLogMessage(string $message) {
    file_put_contents($this->getLogFilePath(), "$message\n", FILE_APPEND);
  }

}
