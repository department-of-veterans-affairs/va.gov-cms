<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface as CommandBuilderInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\QueueInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\BuildTimeRecorderInterface;
use Drupal\va_gov_build_trigger\Service\JenkinsClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BRD Plugin for Environment.
 *
 * @Environment(
 *   id = "brd",
 *   label = @Translation("BRD")
 * )
 */
class BRD extends EnvironmentPluginBase {

  use StringTranslationTrait;

  /**
   * Jenkins Client.
   *
   * @var \Drupal\va_gov_build_trigger\Service\JenkinsClientInterface
   */
  protected $jenkinsClient;

  /**
   * Build Time Recorder.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\BuildTimeRecorderInterface
   */
  protected $buildTimeRecorder;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    Settings $settings,
    StatusInterface $status,
    CommandBuilderInterface $commandBuilder,
    QueueInterface $queue,
    JenkinsClientInterface $jenkinsClient,
    BuildTimeRecorderInterface $buildTimeRecorder
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $logger,
      $settings,
      $status,
      $commandBuilder,
      $queue
    );
    $this->jenkinsClient = $jenkinsClient;
    $this->buildTimeRecorder = $buildTimeRecorder;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.va_gov_build_trigger'),
      $container->get('settings'),
      $container->get('va_gov_build_trigger.frontend_build.status'),
      $container->get('va_gov_build_trigger.frontend_build.command.builder'),
      $container->get('va_gov_build_trigger.frontend_build.command.queue'),
      $container->get('va_gov_build_trigger.jenkins_client'),
      $container->get('va_gov_build_trigger.frontend_build.build_time_recorder')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE): void {
    try {
      $this->jenkinsClient->requestFrontendBuild($front_end_git_ref, $full_rebuild);
      $this->buildTimeRecorder->recordBuildTime();
      $jenkinsJobUrl = $this->settings->get('jenkins_build_job_url');
      $jenkinsJobHost = $this->settings->get('jenkins_build_job_host');
      $jenkinsJobPath = $this->settings->get('jenkins_build_job_path');
      $vars = [
        ':url' => $jenkinsJobUrl,
        '@job_link' => $jenkinsJobHost . $jenkinsJobPath,
      ];
      $message = $this->t('Site rebuild request has been triggered with :url. Please visit <a href="@job_link">@job_link</a> to see status.', $vars);
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
    catch (\Throwable $exception) {
      $message = $this->t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify in #cms-support Slack and please email vacmssupport@va.gov immediately with the error message you see here.', [
        ':url' => $this->settings->get('jenkins_build_job_url'),
      ]);
      $this->messenger()->addError($message);
      $this->logger->error($message);

      $this->getFrontendBuildStatus()->setStatus(FALSE);
      watchdog_exception('va_gov_build_trigger', $exception);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return BrdBuildTriggerForm::class;
  }

}
