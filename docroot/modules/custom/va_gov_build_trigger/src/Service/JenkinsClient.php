<?php

namespace Drupal\va_gov_build_trigger\Service;

use Aws\Ssm\SsmClient;
use Drupal\Core\Site\Settings;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * A client for interfacing with Jenkins.
 */
class JenkinsClient implements JenkinsClientInterface {

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\SettingsInterface
   */
  protected $settings;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterfac
   */
  protected $messenger;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(
    Settings $settings,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->settings = $settings;
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('va_gov_build_trigger');
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
  protected function getRetryMiddleware(int $timeIncrement = 1000, int $retryLimit = 3): Middleware {
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
   * Get an HTTP client.
   *
   * @return \GuzzleHttp\ClientInterface
   *   A configured HTTP client.
   */
  protected function getHttpClient(): ClientInterface {
    $handlerStack = HandlerStack::create();
    $handlerStack->push($this->getRetryMiddleware());
    return new Client([
      'handler' => $handlerStack,
    ]);
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
  protected function getRequestOptions(string $url, string $username, string $password): array {
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
   * {@inheritdoc}
   */
  public function requestFrontendBuild(string $frontendGitRef = NULL, bool $fullRebuild = FALSE): void {
    $jenkinsBuildJobUrl = $this->settings->get('jenkins_build_job_url');
    $githubUsername = $this->settings->get('va_cms_bot_github_username');
    $jenkinsAuthToken = $this->getJenkinsApiToken();
    $requestOptions = $this->getRequestOptions($jenkinsBuildJobUrl, $githubUsername, $jenkinsAuthToken);

    $response = $client->post($jenkinsJobHost, $requestOptions);
    if ($response->getStatusCode() !== 201) {
      throw JenkinsClientException::createWithResponse($response, $jenkinsBuildJobUrl);
    }
  }

  /**
   * Gets the current Jenkins API token.
   *
   * @return string
   *   The value of the value of ssm param.
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
