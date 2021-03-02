<?php

namespace Drupal\va_gov_build_trigger\Service;

/**
 * Updates last-built times on content.
 */
interface BuildTimeRecorderInterface {

  /**
   * Updates the last-built time of all content.
   *
   * @param int $timestamp
   *   A valid UNIX timestamp; seconds since the UNIX epoch.
   */
  public function recordBuildTime(int $timestamp = NULL): void;

}
