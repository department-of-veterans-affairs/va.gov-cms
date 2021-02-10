<?php

namespace Drupal\va_gov_build_trigger\Exception;

use GuzzleHttp\Psr7\Response;

/**
 * A wrapper for exceptions that occur in the Jenkins client.
 */
class JenkinsClientException extends \Exception {

  /**
   * The Jenkins build job URL.
   *
   * @var string
   */
  public $buildJobUrl;

  /**
   * Create with a response object.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   *   An error response.
   * @param string $buildJobUrl
   *   The build job URL.
   *
   * @return \Drupal\va_gov_build_trigger\Exception\JenkinsClientException
   *   An exception of this class.
   */
  public static function createWithResponse(Response $response, string $buildJobUrl): JenkinsClientException {
    $statusCode = $response->getStatusCode();
    $reasonPhrase = $response->getReasonPhrase();
    $message = "Site rebuild failed with status code {$statusCode} {$reasonPhrase} and URL {$buildJobUrl}.";
    $result = new static($message);
    $result->buildJobUrl = $buildJobUrl;
    return $result;
  }

}
