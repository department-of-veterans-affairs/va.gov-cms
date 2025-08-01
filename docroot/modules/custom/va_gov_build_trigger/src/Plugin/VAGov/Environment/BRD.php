<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Error;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_github\Api\Client\ApiClientInterface;
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
   * Github API client for the `content-build` repository.
   *
   * @var \Drupal\va_gov_github\Api\Client\ApiClientInterface
   */
  protected $cbGitHubClient;

  /**
   * Github API client for the `next-build` repository.
   *
   * @var \Drupal\va_gov_github\Api\Client\ApiClientInterface
   */
  protected $nbGitHubClient;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    FileSystemInterface $filesystem,
    Settings $settings,
    ApiClientInterface $cbGitHubClient,
    ApiClientInterface $nbGitHubClient,
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $logger,
      $filesystem,
    );
    $this->settings = $settings;
    $this->cbGitHubClient = $cbGitHubClient;
    $this->nbGitHubClient = $nbGitHubClient;
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
      $container->get('file_system'),
      $container->get('settings'),
      $container->get('va_gov_github.api_client.content_build'),
      $container->get('va_gov_github.api_client.next_build')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild() : void {
    try {
      if ($this->pendingContentBuildWorkflowRunExists()) {
        $vars = [
          '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
        ];
        $message = $this->t('Changes will be included in a content release to VA.gov that\'s already in progress. <a href="@job_link">Check status</a>.', $vars);
      }
      else {
        $this->cbGitHubClient->triggerRepositoryDispatchEvent('content-release');
        $vars = [
          '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
        ];
        $message = $this->t('The system started the process of releasing this content to go live on VA.gov. <a href="@job_link">Check content-build status</a>.', $vars);
      }
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
    catch (\Throwable $exception) {
      $this->handleCbException($exception);
    }
    try {
      if ($this->pendingNextBuildWorkflowRunExists()) {
        $vars = [
          '@job_link' => 'https://github.com/department-of-veterans-affairs/next-build/actions/workflows/content-release-prod.yml',
        ];
        $message = $this->t('Changes will be included in a content release to VA.gov that\'s already in progress. <a href="@job_link">Check status</a>.', $vars);
      }
      else {
        // Trigger the next-build workflow as well.
        $this->nbGitHubClient->triggerRepositoryDispatchEvent('content-release-prod');
        $vars = [
          '@job_link' => 'https://github.com/department-of-veterans-affairs/next-build/actions/workflows/content-release-prod.yml',
        ];
        $message = $this->t('The system started the process of releasing this content to go live on VA.gov. <a href="@job_link">Check next-build status</a>.', $vars);
      }
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
    catch (\Throwable $exception) {
      $this->handleNbException($exception);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function contentEditsShouldTriggerFrontendBuild(): bool {
    return TRUE;
  }

  /**
   * Check for a pending Content Build content-release workflow run.
   */
  protected function pendingContentBuildWorkflowRunExists() : bool {
    try {
      // Check if there are any workflows pending that were created recently.
      $check_interval = 2 * 60 * 60;
      $check_time = time() - $check_interval;
      $workflow_run_params = [
        'status' => 'pending',
        'created' => '>=' . date('c', $check_time),
      ];
      $workflow_runs = $this->cbGitHubClient->getWorkflowRuns('content-release.yml', $workflow_run_params);

      // A well-formed response will have `total_count` set.
      return !empty($workflow_runs['total_count']) && $workflow_runs['total_count'] > 0;
    }
    catch (\Throwable $exception) {
      $this->handleCbException($exception);
    }
    return FALSE;
  }

  /**
   * Check for a pending Next Build content-release workflow run.
   */
  protected function pendingNextBuildWorkflowRunExists() : bool {
    try {
      // Check if there are any workflows pending that were created recently.
      $check_interval = 2 * 60 * 60;
      $check_time = time() - $check_interval;
      $workflow_run_params = [
        'status' => 'pending',
        'created' => '>=' . date('c', $check_time),
      ];
      $workflow_runs = $this->nbGitHubClient->getWorkflowRuns('content-release-prod.yml', $workflow_run_params);

      // A well-formed response will have `total_count` set.
      return !empty($workflow_runs['total_count']) && $workflow_runs['total_count'] > 0;
    }
    catch (\Throwable $exception) {
      $this->handleNbException($exception);
    }
    return FALSE;
  }

  /**
   * Handle GHA API-related exceptions for content-build.
   *
   * @param \Throwable $exception
   *   The exception that was caught.
   */
  protected function handleCbException(\Throwable $exception) : void {
    $message = $this->t('A content release request has failed with an Exception. Please visit <a href="@job_link">@job_link</a> for more information on the issue. If this is the PROD environment please notify in #cms-support Slack and please email support@va-gov.atlassian.net immediately with the error message you see here.', [
      '@job_link' => 'https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml',
    ]);
    $this->messenger()->addError($message);
    $this->logger->error($message);

    Error::logException($this->logger, $exception);
  }

  /**
   * Handle GHA API-related exceptions for next-build.
   *
   * @param \Throwable $exception
   *   The exception that was caught.
   */
  protected function handleNbException(\Throwable $exception) : void {
    $message = $this->t('A content release request has failed with an Exception. Please visit <a href="@job_link">@job_link</a> for more information on the issue. If this is the PROD environment please notify in #cms-support Slack and please email support@va-gov.atlassian.net immediately with the error message you see here.', [
      '@job_link' => 'https://github.com/department-of-veterans-affairs/next-build/actions/workflows/content-release-prod.yml',
    ]);
    $this->messenger()->addError($message);
    $this->logger->error($message);

    Error::logException($this->logger, $exception);
  }

}
