<?php

namespace Drupal\va_gov_content_release\FrontendVersion;

use Drupal\va_gov_content_release\Frontend\FrontendInterface;

/**
 * An interface for the FrontendVersion service.
 *
 * This service allows (in some environments) control over the version of the
 * frontend that is used to perform content releases.
 */
interface FrontendVersionInterface {

  const FRONTEND_VERSION_PREFIX = 'va_gov_content_release.frontend_version.';
  const FRONTEND_VERSION_DEFAULT = '__default';

  /**
   * Get the current version of the frontend.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are requesting.
   *
   * @return string
   *   The current version of the frontend.
   */
  public function getVersion(FrontendInterface $frontend) : string;

  /**
   * Set the current version of the frontend.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are setting.
   * @param string $version
   *   The version to set.
   */
  public function setVersion(FrontendInterface $frontend, string $version) : void;

  /**
   * Reset the current version of the frontend.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are resetting.
   */
  public function resetVersion(FrontendInterface $frontend) : void;

}
