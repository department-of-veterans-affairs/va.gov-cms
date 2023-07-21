<?php

namespace Drupal\va_gov_github\Api\Client;

/**
 * A service that provides access to the Github API for a specific repository.
 *
 * This is primarily used for triggering actions and repository dispatches.
 */
interface ApiClientInterface {

  /**
   * Make a raw GET request.
   *
   * @param string $route
   *   The route.
   * @param array $headers
   *   The headers.
   *
   * @return array|string
   *   The response.
   */
  public function get(string $route, array $headers = []): array|string;

}
