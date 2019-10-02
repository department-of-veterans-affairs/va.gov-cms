<?php

namespace VA\Composer;

use Acquia\Lightning\Composer\Package;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use Symfony\Component\Process\Process;

// Run here so it is loaded as soon as the file is included.
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
    $env_file_name = '.env';

    try {

      // If LANDO Server variable exists, load lando env file.
      if (!empty($_SERVER['LANDO']) && $_SERVER['LANDO'] == 'ON') {

        // Load .env file if it exists first. Otherise use .env.lando.
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
      $dotenv = new Dotenv($env_file_dir, $env_file_name);
      $dotenv->overload();
      $dotenv->required('DRUPAL_ADDRESS')->notEmpty();
      $dotenv->required('BEHAT_PARAMS')->notEmpty();
    }
    catch (\Exception $exception) {
      throw $exception;
    }
  }

  /**
   * Uses curl to confirm that GraphQL endpoint is returning 200 AND is not empty.
   *
   * This test was created to debug a situation where the GraphQL endpoint will
   * fail after content is edited (found during behat testing).
   *
   * More details in the issue below.
   *
   * This will be a good regression test. GraphQL should respond with a non OK
   * HTTP code if there is no content or something wrong.
   *
   * We will use this test to reproduce the bug and perhaps create a workaround, because it will work if you CURL it enough times!
   *
   * A cache rebuild will reset to the error state.
   *
   * @see decoupled.feature
   * @see tests.yml
   * @see https://va-gov.atlassian.net/browse/VAGOV-6070
   *
   * @todo Move to a different handler class to keep things organized.
   */
  static function graphQlTest() {

    // Use DRUPAL_ADDRESS to determine URL
    $graphql_url = getenv('DRUPAL_ADDRESS') . '/graphql';
    $curl_command = "curl --silent --fail --user api:drupal8 --request POST --data-binary '@./scripts/graphql-curl-test-post-body.txt' {$graphql_url}";

    $process = new Process($curl_command);
    $process->mustRun();
    $process->setTimeout(null);

    $output = $process->getOutput();
    if (empty($output)) {
        throw new \Exception("Curl request to $graphql_url succeeded, but GraphQL Output is 0 Bytes! $output. \n Command: " . $process->getCommandLine());
   }
    else {
      print "GraphQL Endpoint returned successfully.";
    }
  }
}


