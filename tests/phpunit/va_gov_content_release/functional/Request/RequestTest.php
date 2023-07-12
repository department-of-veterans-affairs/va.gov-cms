<?php

namespace tests\phpunit\va_gov_content_release\functional\Request;

use Drupal\va_gov_content_release\Request\Request;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Request service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Request\Request
 */
class RequestTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $request = \Drupal::service('va_gov_content_release.request');
    $this->assertInstanceOf(Request::class, $request);
  }

}
