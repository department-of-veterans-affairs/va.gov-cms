<?php

namespace Drupal\va_gov_github\Api\Client\Factory;

use Drupal\va_gov_github\Api\Client\ApiClient;
use Drupal\va_gov_github\Api\Client\ApiClientInterface;
use Drupal\va_gov_github\Api\Settings\ApiSettingsInterface;

/**
 * The GitHub Api Client Factory service.
 *
 * This service is used to create GitHub Api Client instances, and to make
 * these operations testable.
 */
class ApiClientFactory implements ApiClientFactoryInterface {

  const OWNER = 'department-of-veterans-affairs';
  const VA_GOV_CMS = 'va.gov-cms';
  const CONTENT_BUILD = 'content-build';
  const VETS_WEBSITE = 'vets-website';

  /**
   * The settings service.
   *
   * @var \Drupal\va_gov_github\Api\Settings\ApiSettingsInterface
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_github\Api\Settings\ApiSettingsInterface $settings
   *   The settings service.
   */
  public function __construct(ApiSettingsInterface $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function get(string $owner, string $repository, string $apiToken = NULL): ApiClientInterface {
    return new ApiClient($owner, $repository, $apiToken);
  }

  /**
   * {@inheritDoc}
   */
  public function getCms(): ApiClientInterface {
    return $this->get(static::OWNER, static::VA_GOV_CMS, $this->settings->getApiToken());
  }

  /**
   * {@inheritDoc}
   */
  public function getContentBuild(): ApiClientInterface {
    return $this->get(static::OWNER, static::CONTENT_BUILD, $this->settings->getApiToken());
  }

  /**
   * {@inheritDoc}
   */
  public function getVetsWebsite(): ApiClientInterface {
    return $this->get(static::OWNER, static::VETS_WEBSITE, $this->settings->getApiToken());
  }

}
