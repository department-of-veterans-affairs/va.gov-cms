<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
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
   * Date Formatter Service..
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

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
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    WebBuildStatusInterface $webBuildStatus,
    WebBuildCommandBuilder $webBuildCommandBuilder,
    DateFormatterInterface $dateFormatter,
    Connection $database,
    Settings $settings,
    JenkinsClientInterface $jenkinsClient
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $webBuildStatus, $webBuildCommandBuilder);
    $this->dateFormatter = $dateFormatter;
    $this->database = $database;
    $this->settings = $settings;
    $this->jenkinsClient = $jenkinsClient;
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
      $container->get('date.formatter'),
      $container->get('database'),
      $container->get('settings'),
      $container->get('va_gov_build_trigger.jenkins_client')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE): void {
    try {
      $this->jenkinsClient->requestFrontendBuild($front_end_git_ref, $full_rebuild);
      $this->recordBuildTime();
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
    catch (throwable $exception) {
      $message = $this->t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify in #cms-support Slack and please email vacmssupport@va.gov immediately with the error message you see here.', [
        ':url' => $this->settings->get('jenkins_build_job_url'),
      ]);
      $this->messenger()->addError($message);
      $this->logger->error($message);

      $this->webBuildStatus->disableWebBuildStatus();
      watchdog_exception('va_gov_build_trigger', $exception);
    }

  }

  /**
   * Records the build time of the request.
   */
  private function recordBuildTime() {
    // Get our SQL formatted date.
    $time_raw = $this->dateFormatter
      ->format(time(), 'html_datetime', '', 'UTC');
    $time = strtok($time_raw, '+');

    // We only need to update field table - field is set on node import.
    $query = $this->database
      ->update('node__field_page_last_built')
      ->fields(['field_page_last_built_value' => $time]);
    $query->execute();

    // We only need to update - revision field is set on node import.
    $query_revision = $this->database
      ->update('node_revision__field_page_last_built')
      ->fields(['field_page_last_built_value' => $time]);
    $query_revision->execute();
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
