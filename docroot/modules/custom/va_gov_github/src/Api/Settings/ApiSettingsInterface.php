<?php

namespace Drupal\va_gov_github\Api\Settings;

/**
 * An interface for managing GitHub-related settings.
 *
 * These settings (e.g. GitHub API token) are set in the settings files. This
 * abstracts the retrieval of these settings, and makes it testable.
 */
interface ApiSettingsInterface {

  const API_TOKEN_KEY = 'va_cms_bot_github_auth_token';

  /**
   * Returns the GitHub API token.
   *
   * @return string
   *   The GitHub API token.
   *
   * @throws \Drupal\va_gov_github\Exception\NonexistentApiTokenException
   *   If the GitHub API token does not exist.
   */
  public function getApiToken(): string;

}
