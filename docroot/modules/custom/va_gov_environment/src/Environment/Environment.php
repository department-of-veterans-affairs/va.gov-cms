<?php

/**
 * @file
 * Contains the Environment enum.
 */

namespace Drupal\va_gov_environment\Environment;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_environment\Exception\InvalidEnvironmentException;

/**
 * Enum of the possible environments.
 */
enum Environment: string {
  case Ddev = 'ddev';
  case Tugboat = 'tugboat';
  case Staging = 'staging';
  case Prod = 'prod';

  /**
   * Initialize from settings.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings manager.
   *
   * @return \Drupal\va_gov_environment\Environment\Environment
   *   The environment.
   *
   * @throws \Drupal\va_gov_environment\Exception\InvalidEnvironmentException
   *   If an invalid environment is detected.
   */
  public static function fromSettings(Settings $settings): Environment {
    $environment = $settings->get('va_gov_environment')['environment'];
    try {
      return static::from($environment);
    }
    catch (\Throwable $e) {
      throw new InvalidEnvironmentException('Invalid environment detected: ' . $environment);
    }
  }

  /**
   * Is this environment a DDEV environment?
   *
   * @return bool
   *   TRUE if this is a DDEV environment, FALSE otherwise.
   */
  public function isDdev(): bool {
    return match ($this) {
      self::Ddev => TRUE,
      default => FALSE,
    };
  }

  /**
   * Is this environment a local development environment?
   *
   * @return bool
   *   TRUE if this is a local development environment, FALSE otherwise.
   */
  public function isLocalDev(): bool {
    return match ($this) {
      self::Ddev => TRUE,
      default => FALSE,
    };
  }

  /**
   * Is this environment a Tugboat environment?
   *
   * @return bool
   *   TRUE if this is a Tugboat environment, FALSE otherwise.
   */
  public function isTugboat(): bool {
    return match ($this) {
      self::Tugboat => TRUE,
      default => FALSE,
    };
  }

  /**
   * Is this environment a Staging environment?
   *
   * @return bool
   *   TRUE if this is a Staging environment, FALSE otherwise.
   */
  public function isStaging(): bool {
    return match ($this) {
      self::Staging => TRUE,
      default => FALSE,
    };
  }

  /**
   * Is this environment a Prod environment?
   *
   * @return bool
   *   TRUE if this is a Prod environment, FALSE otherwise.
   */
  public function isProduction(): bool {
    return match ($this) {
      self::Prod => TRUE,
      default => FALSE,
    };
  }

}
