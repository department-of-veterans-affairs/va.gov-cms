<?php

namespace tests\phpunit\Migration;

use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm that the VA HC Facility Status Migration works correctly.
 */
class VaHealthCareLocalFacilityStatusMigrationTest extends ExistingSiteBase {

  /**
   * Variable for one-time operations.
   *
   * @var bool
   */
  protected $firstRun = TRUE;

  /**
   * Test the VA Health Care Local Facility Status Migration.
   *
   * This test first imports a new form and verifies that a new node is created.
   * It then re-runs the same migration with updated data and verifies that the
   * updated data has been saved to the node.
   *
   * @dataProvider vaHealthCareLocalFacilityStatusDataProvider
   */
  public function testVaLocalHealthCareFacilityStatusMigration(
    string $migration_id,
    string $bundle,
    string $html,
    array $conditions,
    int $count,
    bool $cleanup
  ) : void {
    // First do the main migration to create the test facility node.
    if ($this->firstRun) {
      $this->firstRun = FALSE;
      $json = file_get_contents(__DIR__ . '/fixtures/health_care_local_facility.json');
      $mockClient = MockHttpClient::create('200', ['Content-Type' => 'application/vnd.geo+json;charset=UTF-8'], $json);
      $this->container->set('http_client', $mockClient);
      Migrator::doImport('va_node_health_care_local_facility');
    }

    // Do the status migration.
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'text/html'], $html);
    $this->container->set('http_client', $mockClient);
    Migrator::doImport($migration_id, ['urls' => ['http://localhost']]);
    $entityCount = EntityStorage::getMatchingEntityCount('node', $bundle, $conditions);
    $this->assertSame($count, $entityCount);

    if ($cleanup) {
      EntityStorage::deleteMatchingEntities('node', $bundle, $conditions);
      Migrator::removeMigrationMapping($migration_id, '999999');
      Migrator::removeMigrationMapping('va_node_health_care_local_facility', 'vha_999999');
    }
  }

  /**
   * Data provider for testVaFormMigration.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function vaHealthCareLocalFacilityStatusDataProvider() : \Generator {
    yield 'Initial migration completes successfully' => [
      'va_node_health_care_local_facility_status',
      'health_care_local_facility',
      file_get_contents(__DIR__ . '/fixtures/health_care_local_facility_status.html'),
      [
        'field_facility_locator_api_id' => 'vha_999999',
        'field_operating_status_facility' => 'normal',
        'field_operating_status_more_info' => 'All Veteran patients are entering through the Atrium area and being screened prior to proceeding to their appointment. Strict visitor restrictions are in place.',
      ],
      1,
      FALSE,
    ];
    yield 'Updated migration completes successfully' => [
      'va_node_health_care_local_facility_status',
      'health_care_local_facility',
      file_get_contents(__DIR__ . '/fixtures/health_care_local_facility_status_updated.html'),
      [
        'field_facility_locator_api_id' => 'vha_999999',
        'field_operating_status_facility' => 'notice',
        'field_operating_status_more_info' => 'For the continued health and safety of all, Bloomington VA Mental Health Clinic patients are getting most of their health care via telehealth, phone appt., VA Video Connect, MyHealtheVet and other technologies during this COVID-19 pandemic.',
      ],
      1,
      TRUE,
    ];
  }

}
