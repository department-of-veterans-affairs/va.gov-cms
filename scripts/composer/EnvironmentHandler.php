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

      // If LANDO Server variable already exists, load lando env file and make sure DRUPAL_ADDRESS is present.
      if (!empty($_SERVER['LANDO']) && $_SERVER['LANDO'] == 'ON') {
        $dotenv = new Dotenv($env_file_dir, EnvironmentHandler::landoEnvironmentFile);

        // Make DRUPAL_ADDRESS and BEHAT_PARAMS required on LANDO only because developers need to see this.
        $dotenv->overload();

        $dotenv->required('DRUPAL_ADDRESS')->notEmpty();
        $dotenv->required('BEHAT_PARAMS')->notEmpty();

      }

      // Otherise, use default (.env) if it exists.
      // If no .env of .env.lando, don't load  variables to avoid error.
      elseif (file_exists($env_file_dir . '/.env')) {
        $dotenv = new Dotenv($env_file_dir);
        $dotenv->overload();
      }

    }
    catch (\Dotenv\Exception\ValidationException $exception) {

      throw $exception;
    }
  }
}


