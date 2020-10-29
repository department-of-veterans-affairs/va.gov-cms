<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Site\Settings;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class VaGovUrl.
 */
class VaGovUrl implements VaGovUrlInterface {

  const WEB_ENVIRONMENTS = [
    'prod' => 'https://www.va.gov',
    'staging' => 'https://staging.va.gov',
    'dev' => 'https://dev.va.gov',
  ];

  /**
   * Http Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Settings Service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructs a new VaGovUrl object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client.
   * @param \Drupal\Core\Site\Settings $settings
   *   The read-only settings container.
   */
  public function __construct(ClientInterface $httpClient, Settings $settings) {
    $this->httpClient = $httpClient;
    $this->settings = $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function getVaGovUrlForEnvironment(String $environment) : string {
    // Allow prod URL to be overridden by settings.php.
    if ($environment === 'prod' && $this->settings->get('va_gov_prod_url', '')) {
      return $this->settings->get('va_gov_prod_url', '');
    }

    return !empty(static::WEB_ENVIRONMENTS[$environment]) ? static::WEB_ENVIRONMENTS[$environment] : '';
  }

  /**
   * {@inheritDoc}
   */
  public function getVaGovUrlForEntity(EntityInterface $entity, String $environment = 'prod') : string {
    try {
      $va_gov_url = $this->getVaGovUrlForEnvironment($environment) . $entity->toUrl()->toString();
      return $va_gov_url;
    }
    catch (Exception $e) {
      return '';
    }
  }

  /**
   * {@inheritDoc}
   */
  public function vaGovUrlForEntityIsLive(EntityInterface $entity, String $environment = 'prod') : bool {
    $va_gov_url = $this->getVaGovUrlForEntity($entity, $environment);

    if (!empty($va_gov_url)) {
      try {
        // Keep the timeout low so that we don't block page loads for too long.
        $response = $this->httpClient->head($va_gov_url, ['connect_timeout' => 2]);

        // Guzzle follows redirects and throws exceptions for 4xx/5xx
        // responses, so we can assume the request was successful.
        return TRUE;
      }
      catch (RequestException $e) {
        return FALSE;
      }
      catch (Exception $e) {
        return FALSE;
      }
    }

    return FALSE;
  }

}
