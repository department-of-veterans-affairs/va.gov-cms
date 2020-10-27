<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Entity\EntityInterface;
use GuzzleHttp\ClientInterface;

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
   * Get the va.gov URL for an environment.
   *
   * @return string
   *   va.gov URL.
   */
  public function getVaGovUrlForEnvironment(String $environment) : string {
    return !empty(self::WEB_ENVIRONMENTS[$environment]) ? self::WEB_ENVIRONMENTS[$environment] : '';
  }

  /**
   * {@inheritDoc}
   */
  public function getVaGovUrlForEntity(EntityInterface $entity, String $environment = 'prod') : string {
    return '';
  }

}
