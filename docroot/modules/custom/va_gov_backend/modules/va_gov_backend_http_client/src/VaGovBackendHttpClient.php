<?php

namespace Drupal\va_gov_backend_http_client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Mock HTTP client for behat tests.
 */
class VaGovBackendHttpClient extends Client {

  /**
   * {@inheritdoc}
   */
  public function head($uri, array $options = []) {
    if (preg_match('/-200$/', $uri)) {
      return new Response(200);
    }
    elseif (preg_match('/-404$/', $uri)) {
      throw RequestException::create(new Request('HEAD', $uri), new Response(404));
    }
    return new Response(200);
  }

}
