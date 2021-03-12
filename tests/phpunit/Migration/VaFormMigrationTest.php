<?php

namespace tests\phpunit\Migration;

use Tests\Support\Entity\Storage as EntityStorage;
use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm that the VA Form Migration works correctly.
 */
class VaFormMigrationTest extends ExistingSiteBase {

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
    bool $cleanup
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
      ],
      1,
      FALSE,
    ];
    yield 'Updated migration completes successfully' => [
      'va_node_form',
      'va_form',
      file_get_contents(__DIR__ . '/fixtures/forms_updated.csv'),
      [
        'field_va_form_row_id' => 999999,
        'field_va_form_title' => 'Test VA Form - Updated',
      ],
      1,
      TRUE,
    ];
  }

}
