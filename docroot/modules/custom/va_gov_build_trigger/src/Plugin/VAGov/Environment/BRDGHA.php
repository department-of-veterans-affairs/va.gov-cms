<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\Service\BuildTimeRecorderInterface;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Drupal\va_gov_consumers\Git\GithubAdapter;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BRD Plugin for Environment (uses GitHub Actions instead of Jenkins jobs).
 *
 * @Environment(
 *   id = "brdgha",
 *   label = @Translation("BRD (GitHub Actions)")
 * )
 */
class BRDGHA extends EnvironmentPluginBase {

  use StringTranslationTrait;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Build Time Recorder.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildTimeRecorderInterface
   */
  protected $buildTimeRecorder;

  /**
   * Github API adapter.
   *
   * @var \Drupal\va_gov_consumers\Git\GithubAdapter
   */
  protected $githubAdapter;

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
    BuildTimeRecorderInterface $buildTimeRecorder,
    GithubAdapter $githubAdapter
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
    $this->buildTimeRecorder = $buildTimeRecorder;
    $this->githubAdapter = $githubAdapter;
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
      $container->get('va_gov_build_trigger.build_time_recorder'),
      $container->get('va_gov.consumers.github.content_build')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE): void {
    $front_end_git_ref = $front_end_git_ref ?? "master";

    try {
      $this->githubAdapter->triggerWorkflow('content-release.yml', $front_end_git_ref, [
        'release_wait' => "0",
        'deploy_environment' => $this->settings->get('github_actions_deploy_env'),
      ]);
      $this->buildTimeRecorder->recordBuildTime();
      $vars = [
        '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
      ];
      $message = $this->t('Site rebuild request has been triggered. Please visit <a href="@job_link">@job_link</a> to see status.', $vars);
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
    catch (\Throwable $exception) {
      $message = $this->t('Site rebuild request has failed with an Exception. Check the logs for the job at <a href="@job_link">@job_link</a> for more information. If this is the PROD environment please notify in #cms-support Slack and please email support@va-gov.atlassian.net immediately with the error message you see here.', [
        '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
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
