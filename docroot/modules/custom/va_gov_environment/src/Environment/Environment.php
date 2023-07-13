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
enum Environment: string implements EnvironmentInterface {
  case Ddev = 'ddev';
  case Tugboat = 'tugboat';
  case Dev = 'dev';
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
    $environment = $settings->get('va_gov_environment')['environment'] ?? '(no value provided)';
    try {
      return static::from($environment);
    }
    catch (\Throwable $e) {
      throw new InvalidEnvironmentException('Invalid environment detected: ' . $environment);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getRawValue(): string {
    return $this->value;
  }

  /**
   * {@inheritDoc}
   */
  public function isDdev(): bool {
    return match ($this) {
      self::Ddev => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isLocalDev(): bool {
    return match ($this) {
      self::Ddev => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isTugboat(): bool {
    return match ($this) {
      self::Tugboat => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isDev(): bool {
    return match ($this) {
      self::Dev => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isStaging(): bool {
    return match ($this) {
      self::Staging => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isProduction(): bool {
    return match ($this) {
      self::Prod => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isBrd(): bool {
    return match ($this) {
      self::Prod => TRUE,
      self::Staging => TRUE,
      self::Dev => TRUE,
      default => FALSE,
    };
  }

}
