<?php

namespace tests\phpunit\Migration;

use Tests\Support\Classes\VaGovExistingSiteBase;
use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;

/**
 * A test to confirm that the VA HC Facility Migration works correctly.
 *
 * @group functional
 * @group all
 * @group facility_migration
 */
class VaHealthCareLocalFacilityMigrationTest extends VaGovExistingSiteBase {

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
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'application/json;charset=UTF-8'], $json);
    $this->container->set('http_client', $mockClient);
    // Each url defined in the migration source configuration will make a fresh
    // call to the mockClient. Guzzle pops each new request off the request
    // queue, so if there is no parity between the number of urls in the source
    // config, and the number of requests expected (queued), an
    // OutOfBoundsException exception with the message 'Mock queue is empty' is
    // empty will be thrown. We avoid this by ensuring there is only one url in
    // the source config. Since there will be no actual http request made,
    // we can set the url to anything.
    $source_config_overrides = ['urls' => 'https://example.com/any/url/will/do'];
    Migrator::doImport($migration_id, $source_config_overrides);
    $entityCount = EntityStorage::getMatchingEntityCount('node', $bundle, $conditions);
    $this->assertSame($count, $entityCount);

    if ($cleanup) {
      EntityStorage::deleteMatchingEntities('node', $bundle, $conditions);
    }
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
