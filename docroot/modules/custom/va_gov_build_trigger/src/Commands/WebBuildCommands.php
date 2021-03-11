<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drush\Commands\DrushCommands;
use Drupal\va_gov_build_trigger\Service\BuildFrontendInterface;
use Drupal\va_gov_build_trigger\CommandExportable;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface;

/**
 * A Drush interface to the Frontend Build dispatcher service.
 */
class WebBuildCommands extends DrushCommands {

  use CommandExportable;

  /**
   * The command builder service.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface
   */
  protected $commandBuilder;

  /**
   * The frontend build service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildFrontendInterface
   */
  protected $buildService;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface $commandBuilder
   *   The web build command builder.
   * @param \Drupal\va_gov_build_trigger\Service\BuildFrontendInterface $buildService
   *   The frontend build dispatcher service.
   */
  public function __construct(
    WebBuildCommandBuilderInterface $commandBuilder,
    BuildFrontendInterface $buildService
  ) {
    $this->commandBuilder = $commandBuilder;
    $this->buildService = $buildService;
  }

  /**
   * {@inheritDoc}
   */
  protected function getWebBuildCommandBuilder(): WebBuildCommandBuilderInterface {
    return $this->commandBuilder;
  }

  /**
   * Dispatch a frontend build.
   *
   * @param string|null $reference
   *   A git reference, or null.
   * @param string $fullRebuild
   *   Will be coerced to a boolean.
   * @param array $options
   *   Command-line options.
   *
   * @command va-gov:build-frontend
   * @aliases va-gov-build-frontend
   * @option dry-run
   *   Don't actually build; just print the commands that would be executed.
   */
  public function buildFrontend(
    string $reference = NULL,
    string $fullRebuild = 'FALSE',
    array $options = [
      'dry-run' => FALSE,
    ]
  ) {
    if (filter_var($reference, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== NULL) {
      $fullRebuild = $reference;
      $reference = NULL;
    }
    if (empty($reference)) {
      $reference = NULL;
    }
    $fullRebuild = filter_var($fullRebuild, FILTER_VALIDATE_BOOLEAN);
    $buildCommands = [];
    if ($fullRebuild && $this->getWebBuildCommandBuilder()->useContentExport()) {
      $newCommands = [
        $this->getExportCommand(),
      ];
      $buildCommands = array_merge($buildCommands, $newCommands);
    }
    $newCommands = $this->getWebBuildCommandBuilder()->buildCommands($reference, $fullRebuild);
    $buildCommands = array_merge($buildCommands, $newCommands);
    if ($options['dry-run']) {
      echo '# $reference: ' . $reference . PHP_EOL;
      echo '# $fullRebuild: ' . ($fullRebuild ? 'TRUE' : 'FALSE') . PHP_EOL;
      foreach ($buildCommands as $buildCommand) {
        echo $buildCommand . PHP_EOL;
      }
    }
    else {
      $this->buildService->triggerFrontendBuild($reference, $fullRebuild);
    }
  }

}
