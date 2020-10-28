<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Entity\EntityInterface;
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
   * Constructs a new VaGovUrl object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client.
   */
  public function __construct(ClientInterface $httpClient) {
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritDoc}
   */
  public function getVaGovUrlForEnvironment(String $environment) : string {
    return !empty(static::WEB_ENVIRONMENTS[$environment]) ? static::WEB_ENVIRONMENTS[$environment] : '';
  }

  /**
   * {@inheritDoc}
   */
  public function getVaGovUrlForEntity(EntityInterface $entity, String $environment = 'prod') : string {
    try {
      $va_gov_url = static::WEB_ENVIRONMENTS[$environment] . $entity->toUrl()->toString();
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
        $response = $this->httpClient->head($va_gov_url, ['connect_timeout' => 2, 'http_errors' => FALSE]);
        if ($response->getStatusCode() == 200) {
          return TRUE;
        }
      }
      catch (RequestException $e) {
        if ($e->hasResponse()) {
          return FALSE;
        }

        return FALSE;
      }
      catch (Exception $e) {
        return FALSE;
      }
    }

    return FALSE;
  }

}
