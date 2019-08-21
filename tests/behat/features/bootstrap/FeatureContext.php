<?php
use DevShop\Behat\DrupalExtension\Context\DevShopDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Dotenv\Dotenv;

$autoloader = require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/docroot/autoload.php';
/**
 * Defines application features from the specific context.
 */
class FeatureContext extends DevShopDrupalContext implements SnippetAcceptingContext {
  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

    // The .env file is only used in CMS-CI and if a developer wants to override
    // any environment variables.

    // Add any Lando-specific and team-wide variables to .env.lando.

    // Load the .env file from the root of the project, if there is one.
    $dotenv_file = dirname(dirname(dirname(dirname(__DIR__)))) . '/.env';
    if (file_exists($dotenv_file)) {
      $dotenv = new Dotenv(dirname(dirname(dirname(dirname(__DIR__)))));
      $dotenv->load();
    }

    parent::__construct();
  }
}
