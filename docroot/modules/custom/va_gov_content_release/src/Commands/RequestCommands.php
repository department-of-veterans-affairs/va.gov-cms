<?php

namespace Drupal\va_gov_content_release\Commands;

use Drupal\va_gov_content_release\Request\RequestInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush interface to the Request service.
 */
class RequestCommands extends DrushCommands {

  /**
   * The Request service.
   *
   * @var \Drupal\va_gov_content_release\Request\RequestInterface
   */
  protected $requestService;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\Request\RequestInterface $requestService
   *   The request service.
   */
  public function __construct(
    RequestInterface $requestService
  ) {
    $this->requestService = $requestService;
  }

  /**
   * Submit a content release request.
   *
   * @param string $reason
   *   The reason for the request.
   *
   * @command va-gov-content-release:request:submit
   * @aliases va-gov-content-release-request-submit
   *   va-gov-content-release-request-submit
   */
  public function submitRequest(string $reason = 'Build requested via Drush.') {
    $this->requestService->submitRequest($reason);
    $this->io()->success('Content Release requested; check the queue for status.');
  }

}
