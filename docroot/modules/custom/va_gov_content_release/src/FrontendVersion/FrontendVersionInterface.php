<?php

namespace Drupal\va_gov_content_release\FrontendVersion;

/**
 * An interface for the FrontendVersion service.
 *
 * This service allows (in some environments) control over the version of the
 * frontend that is used to perform content releases.
 */
interface FrontendVersionInterface {

  const FRONTEND_VERSION = 'va_gov_content_release.frontend_version';
  const FRONTEND_VERSION_DEFAULT = '__default';

  /**
   * Get the current version of the frontend.
   *
   * @return string
   *   The current version of the frontend.
   */
  public function get() : string;

  /**
   * Set the current version of the frontend.
   *
   * @param string $version
   *   The version to set.
   */
  public function set(string $version) : void;

  /**
   * Reset the current version of the frontend.
   */
  public function reset() : void;

}
