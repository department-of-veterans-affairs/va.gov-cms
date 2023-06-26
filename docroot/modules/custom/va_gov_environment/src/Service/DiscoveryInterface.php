<?php

namespace Drupal\va_gov_environment\Service;

/**
 * Interface for the Environment Discovery service.
 *
 * This describes a service that can be used to discover the environment in
 * which the application is running, but is easily mocked for testing.
 */
interface DiscoveryInterface {

  // Differentiate between DDEV and e.g. Lando, Pygmy, etc. We can handle
  // this more abstractly below.
  const ENVIRONMENT_DDEV = 'ddev';

  // Staging environment. As with prod, this may be on the CMS-TEST or CMS
  // infrastructure.
  const ENVIRONMENT_STAGING = 'staging';

  // Production environment. This may be on the CMS-TEST or CMS infrastructure.
  const ENVIRONMENT_PROD = 'prod';

  // Tugboat preview environment.
  const ENVIRONMENT_TUGBOAT = 'tugboat';

  // All environments.
  const ENVIRONMENTS = [
    self::ENVIRONMENT_DDEV,
    self::ENVIRONMENT_STAGING,
    self::ENVIRONMENT_PROD,
    self::ENVIRONMENT_TUGBOAT,
  ];

  /**
   * Get the current raw detected environment.
   *
   * This is not necessarily standardized or reliable.
   *
   * @return string
   *   The current "raw" environment.
   */
  public function getRawEnvironment() : string;

  /**
   * Get the current environment.
   *
   * @return string
   *   The current environment.
   */
  public function getEnvironment() : string;

  /**
   * Check if the current environment is a local development environment.
   *
   * We want to differentiate between local dev and just "local", which could
   * be ambiguous.
   *
   * @return bool
   *   TRUE if the current environment is DDEV, FALSE otherwise.
   */
  public function isLocalDev() : bool;

  /**
   * Check if the current environment is a Tugboat preview environment.
   *
   * @return bool
   *   TRUE if the current environment is Tugboat, FALSE otherwise.
   */
  public function isTugboat() : bool;

  /**
   * Check if the current environment is a staging environment.
   *
   * @return bool
   *   TRUE if the current environment is staging, FALSE otherwise.
   */
  public function isStaging() : bool;

  /**
   * Check if the current environment is a production environment.
   *
   * @return bool
   *   TRUE if the current environment is production, FALSE otherwise.
   */
  public function isProduction() : bool;

  /**
   * Check if the current environment is a CMS-TEST environment.
   *
   * @return bool
   *   TRUE if the current environment is CMS-TEST, FALSE otherwise.
   */
  public function isCmsTest() : bool;

}
