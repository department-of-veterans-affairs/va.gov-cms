<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Core\Site\Settings;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_build_trigger\Exception\JenkinsClientException;
use GuzzleHttp\ClientInterface;

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
   * @var \Drupal\va_gov_build_trigger\Service\SystemsManagerClientInterface
   */
  protected $systemsManagerClient;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory service.
   * @param \Drupal\va_gov_build_trigger\Service\SystemsManagerClientInterface $systemsManagerClient
   *   The systems manager client.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   An HTTP client used to interact directly with Jenkins.
   */
  public function __construct(
    Settings $settings,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $loggerFactory,
    SystemsManagerClientInterface $systemsManagerClient,
    ClientInterface $httpClient
  ) {
    $this->settings = $settings;
    $this->messenger = $messenger;
    $this->logger = $loggerFactory->get('va_gov_build_trigger');
    $this->systemsManagerClient = $systemsManagerClient;
    $this->httpClient = $httpClient;
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
    $jenkinsJobUrl = $this->settings->get('jenkins_build_job_url');
    $githubUsername = $this->settings->get('va_cms_bot_github_username');
    $jenkinsJobHost = $this->settings->get('jenkins_build_job_host');
    $jenkinsAuthToken = $this->systemsManagerClient->getJenkinsApiToken();
    $requestOptions = $this->getRequestOptions($jenkinsJobUrl, $githubUsername, $jenkinsAuthToken);
    $response = $this->httpClient->request('POST', $jenkinsJobHost, $requestOptions);
    if ($response->getStatusCode() !== 201) {
      throw JenkinsClientException::createWithResponse($response, $jenkinsJobUrl);
    }
  }

}
