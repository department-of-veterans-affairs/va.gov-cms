<?php

namespace tests\phpunit\Migration;

use Tests\Support\Classes\VaGovExistingSiteBase;
use Tests\Support\Traits\MigrationTestTrait;

/**
 * A test to confirm that the VA HC Facility Migration works correctly.
 *
 * @group functional
 * @group all
 * @group facility_migration
 */
class VaHealthCareLocalFacilityMigrationTest extends VaGovExistingSiteBase {

  use MigrationTestTrait;

  /**
   * Test the VA Health Care Local Facility Migration.
   *
   * This test first imports a new facility
   * and verifies that a new node is created.
   * It then re-runs the same migration with updated data and verifies that the
   * updated data has been saved to the node.
   *
   * @dataProvider vaHealthCareLocalFacilityDataProvider
   */
  public function testVaLocalHealthCareFacilityMigration(
    string $migration_id,
    string $bundle,
    string $json,
    array $conditions,
    int $count,
    bool $cleanup
  ) : void {
    $this->testMockedJsonDataFetchMigration($migration_id, $bundle, $json, $conditions, $count, $cleanup);
  }

  /**
   * Data provider for testVaLocalHealthCareFacilityMigration.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function vaHealthCareLocalFacilityDataProvider() : \Generator {
    yield 'Initial migration completes successfully' => [
      'va_node_health_care_local_facility',
      'health_care_local_facility',
      file_get_contents(__DIR__ . '/fixtures/health_care_local_facility.json'),
      [
        'field_facility_locator_api_id' => 'vha_999999',
        'field_phone_number' => '309-827-4090',
      ],
      1,
      FALSE,
    ];
    yield 'Updated migration completes successfully' => [
      'va_node_health_care_local_facility',
      'health_care_local_facility',
      file_get_contents(__DIR__ . '/fixtures/health_care_local_facility_updated.json'),
      [
        'field_facility_locator_api_id' => 'vha_999999',
        'field_phone_number' => '309-827-4091',
      ],
      1,
      TRUE,
    ];
  }

}
