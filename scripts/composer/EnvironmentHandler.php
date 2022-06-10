<?php

namespace VA\Composer;

use Dotenv\Dotenv;

// Run here so it is loaded as soon as the file is included.
EnvironmentHandler::load();

/**
 * Sets environment variables from .env files.
 */
class EnvironmentHandler {

  /**
   * Loads .env files if they exist.
   */
  public static function load() {
    $env_file_dir = dirname(dirname(__DIR__));
    $env_file_name = '.env';
    $env_file = $env_file_dir . DIRECTORY_SEPARATOR . $env_file_name;
    $backup_env_file = $env_file_dir . DIRECTORY_SEPARATOR . $env_file_name . '.example';

    try {
      // If the .env file doesn't exist, don't try to load it.
      if (!file_exists($env_file)) {

        // Check for the backup file -- this is checked into git, so it should
        // always be present.
        if (!file_exists($backup_env_file)) {
          return;
        }

        $env_file = $backup_env_file;
      }

      // Load environment and set required params.
      $dotenv = Dotenv::createUnsafeMutable($env_file, $env_file_name);
      $dotenv->safeLoad();
    }
    catch (\Exception $exception) {
      throw $exception;
    }
  }

}
