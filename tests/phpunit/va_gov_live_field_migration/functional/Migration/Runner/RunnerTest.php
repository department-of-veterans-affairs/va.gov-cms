<?php

namespace tests\phpunit\va_gov_live_field_migration\functional\Migration\Runner;

use Drupal\va_gov_live_field_migration\Migration\Runner\Runner;
use Drupal\va_gov_live_field_migration\Migration\Runner\RunnerInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\Migration\Runner\Runner
 */
class RunnerTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $runner = \Drupal::service('va_gov_live_field_migration.migration_runner');
    $this->assertInstanceOf(Runner::class, $runner);
    $this->assertInstanceOf(RunnerInterface::class, $runner);
  }

}
