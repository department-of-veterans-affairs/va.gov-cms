<?php

namespace Tests\Migration;

use Drupal\node\Entity\Node;
use Tests\Support\Migration\Migrator;
use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Mock\HttpClient as MockHttpClient;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm that the VA Facility VBA Migration works correctly.
 *
 * @group functional
 * @group all
 * @group facility_migration
 */
class VaVbaFacilityMigrationTest extends VaGovExistingSiteBase {

  /**
   * Test the VA Facility VBA Migration.
   */
  public function testVaVbaFacilityMigration() : void {
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'application/json;charset=UTF-8'], file_get_contents(__DIR__ . '/fixtures/vba_facility.json'));
    $this->container->set('http_client', $mockClient);
    $source_config_overrides = ['urls' => 'https://example.com/any/url/will/do'];
    Migrator::doImport('va_node_facility_vba', $source_config_overrides);

    $bundle = 'vba_facility';
    $conditions = [
      'field_facility_locator_api_id' => 'vba_000Z',
      'field_official_name' => 'Test VBA Facility',
    ];
    $entities = EntityStorage::getMatchingEntities('node', $bundle, $conditions);

    $this->assertCount(1, $entities);

    $nid = reset($entities);
    $node = Node::load($nid);
    $term_ref = $node->get('field_administration')->getValue();
    $official_name = $node->get('field_official_name')->value;
    $title = $node->getTitle();

    $this->assertEquals($title, $official_name);
    $this->assertEquals('Test VBA Facility', $official_name);
    $this->assertEquals([['target_id' => 191]], $term_ref);

    EntityStorage::deleteMatchingEntities('node', $bundle, $conditions);

  }

}
