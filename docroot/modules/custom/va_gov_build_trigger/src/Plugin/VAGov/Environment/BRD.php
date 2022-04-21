<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Drupal\va_gov_consumers\Git\GithubAdapter;
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
      $container->get('va_gov.consumers.github.content_build')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void {
    $front_end_git_ref = $front_end_git_ref ?? "master";

    try {
      if ($this->pendingWorkflowRunExists()) {
        $vars = [
          '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
        ];
        $message = $this->t('Changes will be included in a content release to VA.gov that\'s already in progress. <a href="@job_link">Check status</a>.', $vars);
      }
      else {
        $this->githubAdapter->triggerWorkflow('content-release.yml', $front_end_git_ref);
        $vars = [
          '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
        ];
        $message = $this->t('The system started the process of releasing this content to go live on VA.gov. <a href="@job_link">Check status</a>.', $vars);
      }
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
    catch (\Throwable $exception) {
      $this->handleBrdException($exception);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function contentEditsShouldTriggerFrontendBuild(): bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return BrdBuildTriggerForm::class;
  }

  /**
   * Check for a pending content-release workflow run.
   */
  protected function pendingWorkflowRunExists() : bool {
    try {
      // Check if there are any workflows pending that were created recently.
      $check_interval = 2 * 60 * 60;
      $check_time = time() - $check_interval;
      $workflow_run_params = [
        'status' => 'pending',
        'created' => '>=' . date('c', $check_time),
      ];
      $workflow_runs = $this->githubAdapter->listWorkflowRuns('content-release.yml', $workflow_run_params);

      // A well-formed response will have `total_count` set.
      return !empty($workflow_runs['total_count']) && $workflow_runs['total_count'] > 0;
    }
    catch (\Throwable $exception) {
      $this->handleBrdException($exception);
    }
    return FALSE;
  }

  /**
   * Handle GHA API-related exceptions.
   *
   * @param \Throwable $exception
   *   The exception that was caught.
   */
  protected function handleBrdException(\Throwable $exception) : void {
    $message = $this->t('A content release request has failed with an Exception. Please visit <a href="@job_link">@job_link</a> for more information on the issue. If this is the PROD environment please notify in #cms-support Slack and please email support@va-gov.atlassian.net immediately with the error message you see here.', [
      '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
    ]);
    $this->messenger()->addError($message);
    $this->logger->error($message);

    $this->webBuildStatus->disableWebBuildStatus();
    watchdog_exception('va_gov_build_trigger', $exception);
  }

}
