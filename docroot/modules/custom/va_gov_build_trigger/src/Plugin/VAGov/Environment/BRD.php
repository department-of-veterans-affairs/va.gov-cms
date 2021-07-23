<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\Service\BuildTimeRecorderInterface;
use Drupal\va_gov_build_trigger\Service\JenkinsClientInterface;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
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
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Jenkins Client.
   *
   * @var \Drupal\va_gov_build_trigger\Service\JenkinsClientInterface
   */
  protected $jenkinsClient;

  /**
   * Build Time Recorder.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildTimeRecorderInterface
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
    WebBuildStatusInterface $webBuildStatus,
    WebBuildCommandBuilder $webBuildCommandBuilder,
    Settings $settings,
    JenkinsClientInterface $jenkinsClient,
    BuildTimeRecorderInterface $buildTimeRecorder
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $logger,
      $webBuildStatus,
      $webBuildCommandBuilder
    );
    $this->settings = $settings;
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
      $container->get('logger.factory')->get('va_gov_build_trigger'),
      $container->get('va_gov.build_trigger.web_build_status'),
      $container->get('va_gov.build_trigger.web_build_command_builder'),
      $container->get('settings'),
      $container->get('va_gov_build_trigger.jenkins_client'),
      $container->get('va_gov_build_trigger.build_time_recorder')
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
      $message = $this->t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify in #cms-support Slack and please email support@va-gov.atlassian.net immediately with the error message you see here.', [
        ':url' => $this->settings->get('jenkins_build_job_url'),
      ]);
      $this->messenger()->addError($message);
      $this->logger->error($message);

      $this->webBuildStatus->disableWebBuildStatus();
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
