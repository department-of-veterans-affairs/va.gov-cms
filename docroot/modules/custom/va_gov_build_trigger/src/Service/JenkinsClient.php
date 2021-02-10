<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Core\Site\Settings;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Exception\JenkinsClientException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * A client for interfacing with Jenkins.
 */
class JenkinsClient implements JenkinsClientInterface {

  use StringTranslationTrait;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The systems manager client.
   *
   * @var \Drupal\Core\va_gov_build_trigger\Service\SystemsManagerClientInterface
   */
  protected $systemsManagerClient;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory service.
   * @param \Drupal\Core\va_gov_build_trigger\Service\SystemsManagerClientInterface $systemsManagerClient
   *   The systems manager client.
   */
  public function __construct(
    Settings $settings,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $loggerFactory,
    SystemsManagerClientInterface $systemsManagerClient
  ) {
    $this->settings = $settings;
    $this->messenger = $messenger;
    $this->logger = $loggerFactory->get('va_gov_build_trigger');
    $this->systemsManagerClient = $systemsManagerClient;
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
   * {@inheritdoc}
   */
  public function getHttpClient(): ClientInterface {
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
  public function requestFrontendBuild(string $frontendGitRef = NULL, bool $fullRebuild = FALSE, ClientInterface $httpClient = NULL): void {
    $jenkinsJobUrl = $this->settings->get('jenkins_build_job_url');
    $githubUsername = $this->settings->get('va_cms_bot_github_username');
    $jenkinsJobHost = $this->settings->get('jenkins_build_job_host');
    $jenkinsAuthToken = $this->systemsManagerClient->getJenkinsApiToken();
    $requestOptions = $this->getRequestOptions($jenkinsJobUrl, $githubUsername, $jenkinsAuthToken);
    if (empty($httpClient)) {
      $httpClient = $this->getHttpClient();
    }
    $response = $httpClient->request('POST', $jenkinsJobHost, $requestOptions);
    if ($response->getStatusCode() !== 201) {
      throw JenkinsClientException::createWithResponse($response, $jenkinsJobUrl);
    }
  }

}
