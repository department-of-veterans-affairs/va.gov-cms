<?php

namespace tests\phpunit\va_gov_content_release\functional\Reporter;

use Drupal\va_gov_content_release\Reporter\Reporter;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Reporter service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Reporter\Reporter
 */
class ReporterTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $reporter = \Drupal::service('va_gov_content_release.reporter');
    $this->assertInstanceOf(Reporter::class, $reporter);
  }

}
