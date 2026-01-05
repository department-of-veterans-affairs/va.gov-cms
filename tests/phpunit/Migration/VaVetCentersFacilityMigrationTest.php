<?php

namespace Tests\Migration;

use Tests\Support\Classes\VaGovExistingSiteBase;
use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;

/**
 * A test to confirm that the VA Facility Vet Centers Migration works correctly.
 *
 * @group functional
 * @group all
 * @group facility_migration
 */
class VaVetCentersFacilityMigrationTest extends VaGovExistingSiteBase {

  /**
   * Test the VA Facility Vet Centers Migration.
   */
  public function testVaVetCentersFacilityMigration() : void {
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'application/json;charset=UTF-8'], file_get_contents(__DIR__ . '/fixtures/vet_centers_facility.json'));
    $this->container->set('http_client', $mockClient);
    $source_config_overrides = ['urls' => 'https://example.com/any/url/will/do'];
    Migrator::doImport('va_node_facility_vet_centers', $source_config_overrides);
    $bundle = 'vet_center';
    $conditions = [
      'field_facility_locator_api_id' => 'vc_0000Z',
      'field_official_name' => 'Test Vet Center Facility',
    ];
    $entities = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties($conditions);
    $this->assertCount(1, $entities);

    $node = reset($entities);
    $term_ref = $node->get('field_administration')->getValue();
    $this->assertEquals([['target_id' => 190]], $term_ref);

    EntityStorage::deleteMatchingEntities('node', $bundle, $conditions);
  }

}
