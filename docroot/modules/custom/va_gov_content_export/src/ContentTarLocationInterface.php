<?php

namespace Drupal\va_gov_content_export;

/**
 * Interface for getting a cotnent tar.
 *
 * @package Drupal\va_gov_content_export
 */
interface ContentTarLocationInterface {

  /**
   * Get the content URI.
   *
   * @return string|null
   *   The uri to the content tar file.
   */
  public function getUri() : ?string;

  /**
   * Get the path to the file.
   *
   * @return string
   *   The path to the content tar.
   */
  public function getFilePath() : string;

  /**
   * Get a files URL.
   *
   * @return string|null
   *   The URL of the file or NULL if not found.
   */
  public function getUrl() : ?string;

}
