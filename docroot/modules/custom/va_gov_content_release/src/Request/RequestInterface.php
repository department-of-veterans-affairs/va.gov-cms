<?php

namespace Drupal\va_gov_content_release\Request;

/**
 * An interface for the Content Release Request service.
 *
 * This service actually enqueues the content release job.
 */
interface RequestInterface {

  const QUEUE_NAME = 'content_release';
  const JOB_TYPE = 'va_gov_content_release_request';

  /**
   * Trigger the content release.
   *
   * @param string $reason
   *   The reason for the content release. This is logged, so it should be
   *   human-readable but can be fairly detailed.
   *
   * @throws \Drupal\va_gov_content_release\Exception\RequestException
   *   If the request could not be submitted.
   */
  public function submitRequest(string $reason) : void;

}
