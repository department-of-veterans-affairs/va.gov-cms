<?php

namespace VA\Composer;

use Acquia\Lightning\Composer\Package;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;

EnvironmentHandler::load();

/**
 * Sets environment variables from .env or .env.lando files.
 */
class EnvironmentHandler {

  const landoEnvironmentFile = '.env.lando';

  /**
   * Loads .env files if they exist.
   */
  static function load() {

    $env_file_dir = dirname(dirname(__DIR__));

    try {

      // If LANDO Server variable exists, load lando env file.
      if (!empty($_SERVER['LANDO']) && $_SERVER['LANDO'] == 'ON') {

        // Load .env file if it exists, otherise use .env.lando.
        if (file_exists($env_file_dir . '/.env')) {
          $dotenv = new Dotenv($env_file_dir);
        }
        else {
          $dotenv = new Dotenv($env_file_dir, EnvironmentHandler::landoEnvironmentFile);
        }
      }

      // If NOT in LANDO, and .env file exists, load it.
      elseif (file_exists($env_file_dir . '/.env')) {
        $dotenv = new Dotenv($env_file_dir);
      }

      // If dotenv files exist, load it and make vars required.
      if (isset($dotenv)) {
        $dotenv->overload();
        $dotenv->required('DRUPAL_ADDRESS')->notEmpty();
        $dotenv->required('BEHAT_PARAMS')->notEmpty();
      }
    }
    catch (\Dotenv\Exception\ValidationException $exception) {

      throw $exception;
    }
  }
}


