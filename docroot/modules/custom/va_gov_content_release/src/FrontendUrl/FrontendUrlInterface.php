<?php

namespace Drupal\va_gov_content_release\FrontendUrl;

/**
 * An interface for the Frontend URL service.
 *
 * This service returns the URL to the frontend.
 */
interface FrontendUrlInterface {

  /**
   * Get the base URL for the frontend.
   *
   * @return string
   *   The base URL for the frontend.
   */
  public function getBaseUrl() : string;

}
