<?php

namespace tests\phpunit\va_gov_content_release\functional\Status;

use Drupal\va_gov_content_release\Status\Status;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Status service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Status\Status
 */
class StatusTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $status = \Drupal::service('va_gov_content_release.status');
    $this->assertInstanceOf(Status::class, $status);
  }

}
