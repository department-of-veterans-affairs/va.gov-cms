<?php

namespace tests\phpunit\Migration;

use Tests\Support\Migration\Migrator;
use Tests\Support\Mock\HttpClient as MockHttpClient;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm that the VA Form Migration works correctly.
 */
class VaFormMigrationTest extends ExistingSiteBase {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Set up.
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeManager = \Drupal::service('entity_type.manager');
  }

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
    int $row_id,
    string $form_title,
    int $count,
    bool $cleanup
  ) : void {
    $mockClient = MockHttpClient::create('200', ['Content-Type' => 'text/csv'], $csv);
    $this->container->set('http_client', $mockClient);
    Migrator::doImport($migration_id);
    $result = $this->queryNodes($bundle, $row_id, $form_title);
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
  public function vaFormDataProvider() : \Generator {
    yield 'Initial migration completes successfully' => [
      'va_node_form',
      'va_form',
      file_get_contents(__DIR__ . '/fixtures/forms.csv'),
      999999,
      'Test VA Form',
      1,
      FALSE,
    ];
    yield 'Updated migration completes successfully' => [
      'va_node_form',
      'va_form',
      file_get_contents(__DIR__ . '/fixtures/forms_updated.csv'),
      999999,
      'Test VA Form - Updated',
      1,
      TRUE,
    ];
  }

  /**
   * Build query for the given parameters.
   *
   * @param string $bundle
   *   The node type.
   * @param int $row_id
   *   The migration row key.
   * @param string $form_title
   *   The form title.
   *
   * @return array
   *   Query result.
   */
  protected function queryNodes($bundle, $row_id, $form_title) : array {
    $node_storage = $this->entityTypeManager->getStorage('node');
    return $node_storage->getQuery()
      ->condition('type', $bundle)
      ->condition('field_va_form_row_id', $row_id)
      ->condition('field_va_form_title', $form_title)
      ->execute();
  }

}
