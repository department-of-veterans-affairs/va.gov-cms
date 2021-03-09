<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drush\Commands\DrushCommands;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface as CommandBuilderInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\DispatcherInterface;

/**
 * A Drush interface to the Frontend Build dispatcher service.
 */
class FrontendBuildCommands extends DrushCommands {

  /**
   * The command builder service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface
   */
  protected $commandBuilder;

  /**
   * The frontend build service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\DispatcherInterface
   */
  protected $dispatcher;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface $commandBuilder
   *   The web build command builder.
   * @param \Drupal\va_gov_build_trigger\FrontendBuild\DispatcherInterface $dispatcher
   *   The frontend build dispatcher service.
   */
  public function __construct(
    CommandBuilderInterface $commandBuilder,
    DispatcherInterface $dispatcher
  ) {
    $this->commandBuilder = $commandBuilder;
    $this->dispatcher = $dispatcher;
  }

  /**
   * Dispatch a frontend build.
   *
   * @param string|null $reference
   *   A git reference, or null.
   * @param string $fullRebuild
   *   Will be coerced to a boolean.
   *
   * @command va-gov:build-frontend
   * @aliases va-gov-build-frontend
   * @option dry-run
   *   Don't actually build; just print the commands that would be executed.
   */
  public function buildFrontend(string $reference = NULL, string $fullRebuild = 'FALSE') {
    if (filter_var($reference, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== NULL) {
      $fullRebuild = $reference;
      $reference = NULL;
    }
    if (empty($reference)) {
      $reference = NULL;
    }
    $fullRebuild = filter_var($fullRebuild, FILTER_VALIDATE_BOOLEAN);
    $buildCommands = $this->commandBuilder->buildCommands($reference, $fullRebuild);
    if ($options['dry-run']) {
      echo '# $reference: ' . $reference . PHP_EOL;
      echo '# $fullRebuild: ' . ($fullRebuild ? 'TRUE' : 'FALSE') . PHP_EOL;
      foreach ($buildCommands as $buildCommand) {
        echo $buildCommand . PHP_EOL;
      }
    }
    else {
      $this->dispatcher->triggerFrontendBuild($reference, $fullRebuild);
    }
  }

}
