<?php

namespace Drupal\va_gov_backend\Deploy;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * An exception which returns a 200.
 *
 * This allows for stopping a HTTP request from bootstrapping Drupal,
 * then returning a 200 with static HTML.
 */
class SuccessHTTPException extends HttpException {

  /**
   * Constructor.
   *
   * @param string $message
   *   The internal exception message.
   * @param \Exception $previous
   *   The previous exception.
   * @param int $code
   *   The internal exception code.
   */
  public function __construct($message = NULL, \Exception $previous = NULL, $code = 0) {
    parent::__construct(200, $message, $previous, [], $code);
  }

}
