<?php

namespace Drupal\va_gov_environment\Service;

use Drupal\Core\Site\Settings;

/**
 * Builds and sends metrics to Datadog.
 */
class Discovery implements DiscoveryInterface {

  /**
   * The Settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings manager.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function getRawEnvironment() : string {
    return $this->settings->get('va_gov_environment')['environment_raw'];
  }

  /**
   * {@inheritDoc}
   */
  public function getEnvironment() : string {
    $environment = $this->settings->get('va_gov_environment')['environment'];
    if (in_array($environment, self::ENVIRONMENTS)) {
      return $environment;
    }
    throw new \Exception('Invalid environment detected: ' . $environment);
  }

  /**
   * {@inheritDoc}
   */
  public function isLocalDev() : bool {
    return $this->getEnvironment() === self::ENVIRONMENT_DDEV;
  }

  /**
   * {@inheritDoc}
   */
  public function isTugboat() : bool {
    return $this->getEnvironment() === self::ENVIRONMENT_TUGBOAT;
  }

  /**
   * {@inheritDoc}
   */
  public function isStaging() : bool {
    return $this->getEnvironment() === self::ENVIRONMENT_STAGING;
  }

  /**
   * {@inheritDoc}
   */
  public function isProduction() : bool {
    return $this->getEnvironment() === self::ENVIRONMENT_PROD;
  }

  /**
   * {@inheritDoc}
   */
  public function isCmsTest() : bool {
    return (bool) $this->settings->get('va_gov_environment')['is_cms_test'];
  }

}
