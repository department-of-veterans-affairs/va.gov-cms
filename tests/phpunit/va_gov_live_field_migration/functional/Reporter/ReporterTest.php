<?php

namespace tests\phpunit\va_gov_live_field_migration\functional\Reporter;

use Drupal\va_gov_live_field_migration\Reporter\Reporter;
use Drupal\va_gov_live_field_migration\Reporter\ReporterInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\Reporter\Reporter
 */
class ReporterTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $reporter = \Drupal::service('va_gov_live_field_migration.reporter');
    $this->assertInstanceOf(Reporter::class, $reporter);
    $this->assertInstanceOf(ReporterInterface::class, $reporter);
  }

}
