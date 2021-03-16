<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\va_gov_build_trigger\CommandExportable;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Drupal\va_gov_build_trigger\Command\CommandRunner;
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
  use QueueHelper;
  use CommandExportable;

  /**
   * The queue storage manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $queueLoader;

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
    EntityStorageInterface $queueLoader
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $webBuildStatus, $webBuildCommandBuilder);
    $this->queueLoader = $queueLoader;
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
      $container->get('entity_type.manager')->getStorage('advancedqueue_queue')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void {
    /** @var \Drupal\advancedqueue\Entity\QueueInterface $queue */
    $queue = $this->queueLoader->load('command_runner');

    // A new command variable since the rebuild commands has been queued.
    $commands = $this->webBuildCommandBuilder->buildCommands($front_end_git_ref);
    $this->queueCommands($commands, $queue);

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
