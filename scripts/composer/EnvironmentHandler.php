<?php

namespace VA\Composer;

use Dotenv\Dotenv;

// Run here so it is loaded as soon as the file is included.
EnvironmentHandler::load();

/**
 * Sets environment variables from .env or .env.lando files.
 */
class EnvironmentHandler {

  /**
   * Loads .env files if they exist.
   */
  public static function load() {

    $env_file_dir = dirname(dirname(__DIR__));
    $env_file_name = '.env';
    try {

      // If LANDO Server variable exists, load Lando env file.
      if (!empty(getenv('LANDO')) && getenv('LANDO') === 'ON') {

        // Load .env file if it exists first. Otherwise use .env.lando.
        if (file_exists($env_file_dir . '/' . $env_file_name)) {
          $env_file_name = '.env';
        }
        else {
          $env_file_name = '.env.lando';
        }
      }

      // If NOT in LANDO, and .env file exists, load it.
      elseif (file_exists($env_file_dir . '/.env')) {
        $env_file_name = '.env';
      }
      else {
        // No lando and .env does not exist: don't load.
        return;
      }

      // Load environment and set required params.
      $dotenv = Dotenv::createUnsafeMutable($env_file_dir, $env_file_name);
      $dotenv->safeLoad();
      $dotenv->required('DRUPAL_ADDRESS')->notEmpty();
      $dotenv->required('BEHAT_PARAMS')->notEmpty();
    }
    catch (\Exception $exception) {
      throw $exception;
    }
  }

}
