<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Drupal\va_gov_content_export\ExportCommand\CommandRunner;
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
  public function triggerFrontendBuild($front_end_git_ref = NULL): void {
    $commands = $this->webBuildCommandBuilder->buildCommands(
      '/var/lib/tugboat',
      $front_end_git_ref
    );

    /** @var \Drupal\advancedqueue\Entity\QueueInterface $queue */
    $queue = $this->queueLoader->load('command_runner');
    $this->queueCommands($commands, $queue);

    $this->messenger()->addStatus('A request to rebuild the front end has been submitted.');
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return TugboatBuildTriggerForm::class;
  }

}
