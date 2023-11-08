<?php

namespace Drupal\va_gov_content_release\FrontendUrl;

use Drupal\Core\Site\Settings;

/**
 * The frontend URL service.
 *
 * This service returns the URL to the frontend.
 */
class FrontendUrl implements FrontendUrlInterface {

  /**
   * The settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings service.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * Get the base URL for the frontend.
   *
   * @return string
   *   The base URL for the frontend.
   */
  public function getBaseUrl() : string {
    return $this->settings->get('va_gov_frontend_url') ?? 'https://www.va.gov';
  }

}
