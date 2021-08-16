<?php

namespace Tests\Support\Mock;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * A helper class to mock Drupal's httpClient.
 */
class HttpClient {

  /**
   * Mock the http client.
   *
   * @param string $response_code
   *   The HTTP response code to return.
   * @param array $headers
   *   The headers to return.
   * @param string $body
   *   The body to return.
   *
   * @return \GuzzleHttp\Client
   *   Mocked Guzzle Client.
   */
  public static function create(
      string $response_code = '200',
      array $headers = [],
      string $body = ''
    ) : Client {

    // Create a mock and queue response.
    $response = new Response($response_code, $headers, $body);
    $mock = new MockHandler([$response]);
    $handler_stack = HandlerStack::create($mock);
    $history_container = [];
    $history = Middleware::history($history_container);
    $handler_stack->push($history);
    return new Client(['handler' => $handler_stack]);
  }

}
