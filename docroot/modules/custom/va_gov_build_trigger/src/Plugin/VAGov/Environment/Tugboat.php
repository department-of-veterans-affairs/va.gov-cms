<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\CommandExportable;
use Drupal\va_gov_build_trigger\Command\CommandRunner;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;
use Drupal\va_gov_build_trigger\Service\WebBuildCommandQueueInterface;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tugboat Plugin for Environment.
 *
 * @Environment(
 *   id = "tugboat",
 *   label = @Translation("tugboat")
 * )
 */
class Tugboat extends EnvironmentPluginBase {
  use CommandRunner;
  use CommandExportable;

  /**
   * The queue service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\WebBuildCommandQueueInterface
   */
  protected $queueService;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    WebBuildStatusInterface $webBuildStatus,
    WebBuildCommandBuilder $webBuildCommandBuilder,
    WebBuildCommandQueueInterface $queueService
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $webBuildStatus, $webBuildCommandBuilder);
    $this->queueService = $queueService;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('va_gov_build_trigger'),
      $container->get('va_gov.build_trigger.web_build_status'),
      $container->get('va_gov.build_trigger.web_build_command_builder'),
      $container->get('va_gov_build_trigger.web_build_command_queue')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void {
    if ($full_rebuild && $this->webBuildCommandBuilder->useContentExport()) {
      $this->queueService->enqueueCommands([
        $this->getExportCommand(),
      ]);
    }

    // A new command variable since the rebuild commands has been queued.
    $commands = $this->webBuildCommandBuilder->buildCommands($front_end_git_ref);
    $this->queueService->enqueueCommands($commands);

    $this->messenger()->addStatus('A request to rebuild the front end has been submitted.');
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild() : bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return TugboatBuildTriggerForm::class;
  }

}
