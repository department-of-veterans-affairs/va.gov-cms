<?php

namespace tests\phpunit\Migration;

use Drupal\node\Entity\Node;
use Tests\Support\Classes\VaGovExistingSiteBase;
use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;

/**
 * A test to confirm that the VA Form Migration works correctly.
 *
 * @group functional
 * @group all
 */
class VaFormMigrationTest extends VaGovExistingSiteBase {

  /**
   * Test the VA Form Migration.
   *
   * This test first imports a new form and verifies that a new node is created.
   * It then re-runs the same migration with updated data and verifies that the
   * updated data has been saved to the node.
   *
   * @dataProvider vaFormDataProvider
   */
  public function testVaFormMigration(
    string $migration_id,
    string $bundle,
    string $csv,
    array $conditions,
    int $count,
    bool $cleanup,
  ) : void {
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'text/csv'], $csv);
    $this->container->set('http_client', $mockClient);
    Migrator::doImport($migration_id);
    $entityCount = EntityStorage::getMatchingEntityCount('node', $bundle, $conditions);
    $this->assertSame($count, $entityCount);

    if ($cleanup) {
      EntityStorage::deleteMatchingEntities('node', $bundle, $conditions);
    }
  }

  /**
   * Data provider for testVaFormMigration.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function vaFormDataProvider() : \Generator {
    yield 'Initial migration completes successfully' => [
      'va_node_form',
      'va_form',
      file_get_contents(__DIR__ . '/fixtures/forms.csv'),
      [
        'field_va_form_row_id' => 999999,
        'field_va_form_title' => 'Test VA Form',
        'title' => 'About VA Form 99-9999',
        'field_va_form_page_title' => 'VA Form 99-9999',
      ],
      1,
      FALSE,
    ];
    yield 'Updated migration overwrites DB fields but preserves page title' => [
      'va_node_form',
      'va_form',
      file_get_contents(__DIR__ . '/fixtures/forms_updated.csv'),
      [
        'field_va_form_row_id' => 999999,
        'field_va_form_title' => 'Test VA Form - Updated',
        // Title is in overwrite_properties so it reflects the new displayName.
        'title' => 'About VA Form 99-9999-UPDATED',
        // field_va_form_page_title is NOT in overwrite_properties, so if the
        // migration overwrote this field the value would be
        // 'VA Form 99-9999-UPDATED'. Asserting the original value confirms
        // the migration leaves the field alone on re-import.
        'field_va_form_page_title' => 'VA Form 99-9999',
      ],
      1,
      TRUE,
    ];
  }

  /**
   * Test that an editor-set page title is not overwritten on migration re-run.
   *
   * Runs an initial import, simulates an editor override of
   * field_va_form_page_title to a value the migration would never set, then
   * re-runs the migration with a fixture where displayName differs. Asserts
   * the custom value survives the re-import unchanged.
   */
  public function testVaFormPageTitlePreservedOnReimport() : void {
    // Step 1: Run initial import.
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'text/csv'], file_get_contents(__DIR__ . '/fixtures/forms.csv'));
    $this->container->set('http_client', $mockClient);
    Migrator::doImport('va_node_form');

    // Step 2: Simulate an editor override of the page title.
    $nids = EntityStorage::getMatchingEntities('node', 'va_form', [
      'field_va_form_row_id' => 999999,
    ]);
    $this->assertCount(1, $nids, 'One node created by initial import.');
    $node = Node::load(reset($nids));
    $custom_page_title = 'Custom Editor Page Title';
    $node->set('field_va_form_page_title', $custom_page_title);
    $node->save();

    // Step 3: Re-run migration with a fixture where displayName differs from
    // both the original source value and the custom editor value. If the
    // migration overwrote the field, the value would change.
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'text/csv'], file_get_contents(__DIR__ . '/fixtures/forms_updated.csv'));
    $this->container->set('http_client', $mockClient);
    Migrator::doImport('va_node_form');

    // Step 4: Assert the editor-set value was not overwritten.
    $node = Node::load($node->id());
    $this->assertEquals(
      $custom_page_title,
      $node->get('field_va_form_page_title')->value,
      'Editor-set page title is not overwritten by migration re-import.'
    );

    EntityStorage::deleteMatchingEntities('node', 'va_form', ['field_va_form_row_id' => 999999]);
  }

}
