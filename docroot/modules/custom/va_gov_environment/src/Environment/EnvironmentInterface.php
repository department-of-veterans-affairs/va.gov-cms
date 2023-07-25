<?php

namespace Drupal\va_gov_environment\Environment;

/**
 * Interface for the Environment enum.
 */
interface EnvironmentInterface {

  /**
   * Get the raw value of the current environment.
   *
   * @return string
   *   The raw value of the current environment.
   */
  public function getRawValue() : string;

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
   * Check if the current environment is a dev environment.
   *
   * @return bool
   *   TRUE if the current environment is dev, FALSE otherwise.
   */
  public function isDev() : bool;

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
   * Check if the current environment is a BRD environment.
   *
   * @return bool
   *   TRUE if the current environment is BRD, FALSE otherwise.
   */
  public function isBrd() : bool;

}
