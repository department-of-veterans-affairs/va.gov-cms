<?php


namespace Drupal\va_gov_backend\Exception;


use Symfony\Component\HttpKernel\Exception\HttpException;

class SuccessHTTPException extends HttpException {
  /**
   * @param string     $message  The internal exception message
   * @param \Exception $previous The previous exception
   * @param int        $code     The internal exception code
   */
  public function __construct($message = null, \Exception $previous = null, $code = 0)
  {
    parent::__construct(200, $message, $previous, [], $code);
  }
}
