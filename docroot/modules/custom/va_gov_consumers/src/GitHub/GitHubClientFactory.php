<?php

namespace Drupal\va_gov_consumers\GitHub;

use Drupal\Core\Site\Settings;
use GuzzleHttp\Client as HttpClient;

/**
 * A factory service for creating GitHubClient services.
 */
class GitHubClientFactory implements GitHubClientFactoryInterface {

  /**
   * The Drupal Settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * GitHubClientFactory constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   * @param \GuzzleHttp\Client $httpClient
   *   The HTTP client.
   */
  public function __construct(Settings $settings, HttpClient $httpClient) {
    $this->settings = $settings;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public function getClient(string $repositoryPath, string $tokenSettingName) : GitHubClientInterface {
    if (empty($repositoryPath)) {
      throw new \InvalidArgumentException('Invalid GitHub Repository Path');
    }
    if (empty($tokenSettingName)) {
      throw new \InvalidArgumentException('Invalid GitHub Token Setting Name');
    }
    $token = $this->settings->get($tokenSettingName);
    if (empty($token)) {
      throw new \InvalidArgumentException('Invalid GitHub Token');
    }
    return new GitHubClient($repositoryPath, $token, $this->httpClient);
  }

}
