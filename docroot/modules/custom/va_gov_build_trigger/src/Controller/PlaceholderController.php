<?php

namespace Drupal\va_gov_build_trigger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Placeholder for content release.
 *
 * @todo Remove this in #8934.
 */
class PlaceholderController extends ControllerBase {

  /**
   * Return an HTTP 200.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   An HTTP response.
   */
  public function placeholder() {
    return Response::create('placeholder', 200);
  }

}
