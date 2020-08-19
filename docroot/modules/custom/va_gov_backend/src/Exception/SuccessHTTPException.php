<?php

namespace Drupal\va_gov_backend\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * An excpetion which returns a 200.
 *
 * This allows for stopping a HTTP request and returning a 200 and static HTML.
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
