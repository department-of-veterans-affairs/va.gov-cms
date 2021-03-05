<?php

namespace Drupal\va_gov_build_trigger\Service;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;

/**
 * A factory for creating Guzzle middleware.
 */
class MiddlewareFactory {

  /**
   * Construct a middleware that retries failed requests.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger channel.
   * @param int $timeIncrement
   *   Time added to successive retries in milliseconds.
   * @param int $retryLimit
   *   The maximum number of retries.
   *
   * @return \Closure
   *   The retry middleware.
   *
   * @see http://dawehner.github.io/php,/guzzle/2017/05/19/guzzle-retry.html
   */
  public static function createRetryMiddleware(LoggerInterface $logger, int $timeIncrement = 1000, int $retryLimit = 3): \Closure {
    return Middleware::retry(function ($retry, $request, $response, $reason) use ($logger, $retryLimit) {
      // Must be a "201 Created" response code & message, if not then cont
      // and retry.
      if ($response && $response->getStatusCode() === 201) {
        return FALSE;
      }
      $logger->warning('Retry site build - attempt #' . $retry);
      return $retry < $retryLimit;
    }, function ($retry) use ($timeIncrement) {
      return $retry * $timeIncrement;
    });
  }

  /**
   * Construct a middleware handler stack.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger channel.
   *
   * @return \GuzzleHttp\HandlerStack
   *   The handler stack.
   */
  public static function createHandlerStack(LoggerInterface $logger): HandlerStack {
    $result = HandlerStack::create();
    $result->push(static::createRetryMiddleware($logger));
    return $result;
  }

}
