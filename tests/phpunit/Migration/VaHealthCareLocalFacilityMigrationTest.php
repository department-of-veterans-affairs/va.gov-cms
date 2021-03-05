<?php

namespace tests\phpunit\Migration;

use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Plugin\MigrationInterface;
use Tests\Support\Mock\HttpClient as MockHttpClient;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm that the VA HC Facility Migration works correctly.
 */
class VaHealthCareLocalFacilityMigrationTest extends ExistingSiteBase {

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
   * Test the VA Health Care Local Facility Migration.
   *
   * This test first imports a new form and verifies that a new node is created.
   * It then re-runs the same migration with updated data and verifies that the
   * updated data has been saved to the node.
   *
   * @dataProvider vaHealthCareLocalFacilityDataProvider
   */
  public function testVaLocalHealthCareFacilityMigration(
    string $migration_id,
    string $bundle,
    string $json,
    string $api_id,
    string $phone_number,
    int $count,
    bool $cleanup
  ) : void {
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'application/vnd.geo+json;charset=UTF-8'], $json);
    $this->container->set('http_client', $mockClient);
    $this->doImport($migration_id);
    $result = $this->queryNodes($bundle, $api_id, $phone_number);
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
  public function vaHealthCareLocalFacilityDataProvider() : \Generator {
    yield 'Initial migration completes successfully' => [
      'va_node_health_care_local_facility',
      'health_care_local_facility',
      file_get_contents(__DIR__ . '/fixtures/health_care_local_facility.json'),
      'vha_999999',
      '309-827-4090',
      1,
      FALSE,
    ];
    yield 'Updated migration completes successfully' => [
      'va_node_health_care_local_facility',
      'health_care_local_facility',
      file_get_contents(__DIR__ . '/fixtures/health_care_local_facility_updated.json'),
      'vha_999999',
      '309-827-4091',
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
   * @param string $api_id
   *   The migration row key.
   * @param string $phone_number
   *   The phone number.
   *
   * @return array
   *   Query result.
   */
  protected function queryNodes($bundle, $api_id, $phone_number) : array {
    $node_storage = $this->entityTypeManager->getStorage('node');
    return $node_storage->getQuery()
      ->condition('type', $bundle)
      ->condition('field_facility_locator_api_id', $api_id)
      ->condition('field_phone_number', $phone_number)
      ->execute();
  }

}
