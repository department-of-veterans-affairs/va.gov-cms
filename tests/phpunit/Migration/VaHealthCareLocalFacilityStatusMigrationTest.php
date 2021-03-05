<?php

namespace tests\phpunit\Migration;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Tests\Mock\HttpClient as MockHttpClient;
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
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Migration manager service.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $migrationManager;

  /**
   * Set up.
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->migrationManager = \Drupal::service('plugin.manager.migration');
  }

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
      $this->doImport('va_node_health_care_local_facility');
    }

    // Do the status migration.
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'text/html'], $html);
    $this->container->set('http_client', $mockClient);
    $this->doImport($migration_id);
    $result = $this->queryNodes($bundle, $conditions);
    $this->assertCount($count, $result);

    if ($cleanup) {
      $node = $this->entityTypeManager->getStorage('node')->load(reset($result));
      $node->delete();
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

  /**
   * Run an import for the given migration ID.
   *
   * @param string $migration_id
   *   The migration machine name.
   */
  protected function doImport($migration_id) : void {
    $migration = $this->migrationManager->createInstance($migration_id);

    $source_config = $migration->getSourceConfiguration();
    $source_config['urls'] = reset($source_config['urls']);
    $migration->set('source', $source_config);

    $status = $migration->getStatus();
    if ($status !== MigrationInterface::STATUS_IDLE) {
      $migration->setStatus(MigrationInterface::STATUS_IDLE);
    }
    $migration->getIdMap()->prepareUpdate();
    $executable = new MigrateExecutable($migration, new MigrateMessage());
    $executable->import();
  }

  /**
   * Build query for the given parameters.
   *
   * @param string $bundle
   *   The node type.
   * @param array $conditions
   *   Additional query conditions.
   *
   * @return array
   *   Query result.
   */
  protected function queryNodes($bundle, array $conditions) : array {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()->condition('type', $bundle);

    foreach ($conditions as $key => $value) {
      $query->condition($key, $value);
    }

    return $query->execute();
  }

}
