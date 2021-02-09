<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Aws\Ssm\SsmClient;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
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
   * @var \Drupal\Core\Site\SettingsInterface
   */
  protected $settings;

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
    SettingsInterface $settings
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger, $webBuildStatus, $webBuildCommandBuilder);
    $this->dateFormatter = $dateFormatter;
    $this->database = $database;
    $this->settings = $settings;
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
      $container->get('settings')
    );
  }

  /**
   * Build the cURL request options.
   *
   * @param string $url
   *   The request URL.
   * @param string $username
   *   The username.
   * @param string $password
   *   The password.
   *
   * @return array
   *   Request options for the Jenkins build.
   */
  public function getRequestOptions(string $url, string $username, string $password): array {
    return [
      'verify' => TRUE,
      'body' => '',
      'headers' => [
        'Accept' => 'text/plain',
      ],
      'curl' => [
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
        CURLOPT_VERBOSE => TRUE,
        CURLOPT_URL => $url,
        CURLOPT_POST => 1,
        CURLOPT_USERNAME => $username,
        CURLOPT_PASSWORD => $password,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 1,
      ],
    ];
  }

  /**
   * Construct a middleware that retries failed requests.
   *
   * @param int $timeIncrement
   *   Time added to successive retries in milliseconds.
   * @param int $retryLimit
   *   The maximum number of retries.
   *
   * @return \GuzzleHttp\Middleware
   *   The retry middleware.
   *
   * @see http://dawehner.github.io/php,/guzzle/2017/05/19/guzzle-retry.html
   */
  public function getRetryMiddleware(int $timeIncrement = 1000, int $retryLimit = 3): Middleware {
    return Middleware::retry(function ($retry, $request, $response, $reason) use ($retryLimit) {
      // Must be a "201 Created" response code & message, if not then cont
      // and retry.
      if ($response && $response->getStatusCode() === 201) {
        return FALSE;
      }
      $this->logger->warning('Retry site build - attempt #' . $retry);
      return $retry < $retryLimit;
    }, function ($retry) use ($timeIncrement) {
      return $retry * $timeIncrement;
    });
  }

  /**
   * Construct a client to make the request to Jenkins.
   *
   * @param \GuzzleHttp\HandlerStack $handlerStack
   *   The middleware used with this client.
   *
   * @return \GuzzleHttp\ClientInterface
   *   An HTTP client.
   */
  public function getJenkinsClient(HandlerStack $handlerStack): ClientInterface {
    return new Client([
      'handler' => $handlerStack,
    ]);
  }

  /**
   * Request a front end build.
   *
   * @param string $githubUsername
   *   The GitHub username used to integrate with Jenkins.
   * @param string $jenkinsJobUrl
   *   The build job URL.
   * @param string $jenkinsAuthToken
   *   The auth token used with Jenkins.
   * @param string $jenkinsJobHost
   *   The Jenkins build job host.
   * @param string $jenkinsJobPath
   *   The Jenkins build job path.
   * @param string $frontendGitRef
   *   The git ref of the frotnend.
   * @param bool $fullRebuild
   *   Whether or not a full rebuild should be requested.
   */
  public function requestFrontendBuild(
    string $githubUsername,
    string $jenkinsJobUrl,
    string $jenkinsAuthToken,
    string $jenkinsJobHost,
    string $jenkinsJobPath,
    string $frontendGitRef = NULL,
    bool $fullRebuild = FALSE
  ): void {
    $handlerStack = HandlerStack::create();
    $handlerStack->push($this->getRetryMiddleware());
    $client = $this->getJenkinsClient($handlerStack);
    $requestOptions = $this->getRequestOptions($githubUsername, $jenkinsJobUrl, $jenkinsAuthToken);
    $response = $client->post($jenkinsJobHost, $requestOptions);
    if ($response->getStatusCode() !== 201) {
      $vars = [
        ':status_code' => $response->getStatusCode(),
        ':reason_phrase' => $response->getReasonPhrase(),
        ':url' => $jenkinsJobUrl,
      ];

      $message = $this->t('Site rebuild failed with status code {:status_code} {:reason_phrase} and URL {:url}.', $vars);
      $this->messenger()->addError($message);
      $this->logger->error($message);

      $message = $this->t('Site rebuild request has failed for :url, check log for more information.', [
        ':url' => $jenkinsJobUrl,
      ]);
      $this->messenger()->addError($message);
      $this->logger->error($message);
    }
    else {
      $this->recordBuildTime();
      $vars = [
        ':url' => $jenkinsJobUrl,
        '@job_link' => $jenkinsJobHost . $jenkinsJobPath,
      ];

      $message = $this->t('Site rebuild request has been triggered with :url. Please visit <a href="@job_link">@job_link</a> to see status.', $vars);
      $this->messenger()->addStatus($message);
      $this->logger->info($message);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE): void {
    $githubUsername = $this->settings->get('va_cms_bot_github_username');
    $jenkinsAuthToken = $this->getJenkinsApiToken();
    $jenkinsJobUrl = $this->settings->get('jenkins_build_job_url');
    $jenkinsJobHost = $this->settings->get('jenkins_build_job_host');
    $jenkinsJobPath = $this->settings->get('jenkins_build_job_path');

    try {
      $this->requestFrontendBuild($githubUsername, $jenkinsJobUrl, $jenkinsJobAuthToken, $jenkinsJobHost, $jenkinsJobPath, $front_end_git_ref, $full_rebuild);
    }
    catch (RequestException $exception) {

      $message = $this->t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify in #cms-support Slack and please email vacmssupport@va.gov immediately with the error message you see here.', [':url' => $jenkinsJobUrl]);
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

  /**
   * Gets the current Jenkins API token.
   *
   * @return string
   *   The value of the value of ssm param named
   *   '/cms/va-cms-bot/jenkins-api-token', or '' if not found.
   */
  public function getJenkinsApiToken(): string {
    try {
      $client = new SsmClient([
        'version' => 'latest',
        'region' => 'us-gov-west-1',
      ]);
      $result = $client->getParameter([
        'Name' => '/cms/va-cms-bot/jenkins-api-token',
        'WithDecryption' => TRUE,
      ]);
      return $result['Parameter']['Value'];
    }
    catch (\Exception $exception) {
      $message = $this->t('Failed to retrieve the Jenkins API token.  The error encountered was @message', [
        '@message' => $exception->getMessage(),
      ]);
      $exception = new \Exception($message);
      watchdog_exception('va_gov_build_trigger', $exception);
      throw $exception;
    }
  }

}
