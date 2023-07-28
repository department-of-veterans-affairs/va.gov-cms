<?php

namespace Drupal\va_gov_github\Api\Settings;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_github\Exception\NonexistentApiTokenException;

/**
 * A service for managing GitHub-related settings.
 *
 * These settings (e.g. GitHub API token) are set in the settings files. This
 * abstracts the retrieval of these settings, and makes it testable.
 */
class ApiSettings implements ApiSettingsInterface {

  /**
   * Drupal Settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal Settings service.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function getApiToken(): string {
    $apiToken = $this->settings->get(ApiSettingsInterface::API_TOKEN_KEY);
    if (empty($apiToken)) {
      throw new NonexistentApiTokenException('GitHub API token does not exist.');
    }
    return $apiToken;
  }

}
