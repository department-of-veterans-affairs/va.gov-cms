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

    // Load the defaults. They're checked into Git, so they should always be
    // present.
    try {
      // If the .env file doesn't exist, don't try to load it.
      if (!file_exists($env_file)) {
        return;
      }

      // Load environment and set required params.
      $dotenv = Dotenv::createUnsafeMutable($env_file, $env_file_name);
      $dotenv->safeLoad();
      $dotenv->required('DRUPAL_ADDRESS')->notEmpty();
      $dotenv->required('BEHAT_PARAMS')->notEmpty();
    }
    catch (\Exception $exception) {
      throw $exception;
    }
  }

}
