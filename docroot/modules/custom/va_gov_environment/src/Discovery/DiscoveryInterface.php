<?php

namespace Drupal\va_gov_environment\Discovery;

use Drupal\va_gov_environment\Environment\EnvironmentInterface;

/**
 * Interface for the Environment Discovery service.
 *
 * This describes a service that can be used to discover the environment in
 * which the application is running, but is easily mocked for testing.
 */
interface DiscoveryInterface extends EnvironmentInterface {

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
   * @return \Drupal\va_gov_environment\Environment\EnvironmentInterface
   *   The current environment.
   */
  public function getEnvironment() : EnvironmentInterface;

  /**
   * Check if the current environment is a CMS-TEST environment.
   *
   * @return bool
   *   TRUE if the current environment is CMS-TEST, FALSE otherwise.
   */
  public function isCmsTest() : bool;

}
