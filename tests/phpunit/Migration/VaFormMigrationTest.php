<?php

namespace tests\phpunit\Migration;

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
   * The row id used for the forward draft migration fixture.
   */
  private const FORWARD_DRAFT_ROW_ID = 999998;

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
      if (isset($conditions['field_va_form_row_id'])) {
        Migrator::removeMigrationMapping($migration_id, (string) $conditions['field_va_form_row_id']);
      }
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

  /**
   * Test rerunning the form migration with a published node plus forward draft.
   */
  public function testVaFormMigrationPreservesForwardDraft() : void {
    $migration_id = 'va_node_form';
    $tool_url = 'https://www.va.gov/find-forms-test-tool';
    $conditions = ['field_va_form_row_id' => self::FORWARD_DRAFT_ROW_ID];

    try {
      $initial_csv = file_get_contents(__DIR__ . '/fixtures/forms_forward_draft.csv');
      $updated_csv = file_get_contents(__DIR__ . '/fixtures/forms_forward_draft_updated.csv');

      $this->container->set('http_client', MockHttpClient::create('200', ['Content-Type' => 'text/csv'], $initial_csv));
      Migrator::doImport($migration_id);

      $nids = EntityStorage::getMatchingEntities('node', 'va_form', $conditions);
      $this->assertCount(1, $nids);

      $storage = \Drupal::entityTypeManager()->getStorage('node');
      $nid = (int) reset($nids);

      /** @var \Drupal\node\NodeInterface $node */
      $node = $storage->load($nid);
      $node->setPublished(TRUE);
      $node->set('moderation_state', 'published');
      $node->save();

      $node = $storage->load($nid);
      $node->set('field_va_form_tool_url', ['uri' => $tool_url]);
      $node->set('moderation_state', 'draft');
      $node->setUnpublished();
      $node->save();

      $published_default_revision_id = (int) $storage->load($nid)->getRevisionId();
      $previous_latest_revision_id = (int) $storage->getLatestRevisionId($nid);
      $previous_latest_revision = $storage->loadRevision($previous_latest_revision_id);

      $this->assertNotSame($published_default_revision_id, $previous_latest_revision_id);
      $this->assertSame('draft', $previous_latest_revision->get('moderation_state')->value);
      $this->assertSame($tool_url, $previous_latest_revision->get('field_va_form_tool_url')->first()->getValue()['uri']);

      $this->container->set('http_client', MockHttpClient::create('200', ['Content-Type' => 'text/csv'], $updated_csv));
      Migrator::doImport($migration_id);

      /** @var \Drupal\node\NodeInterface $default_revision */
      $default_revision = $storage->load($nid);
      $latest_revision_id = (int) $storage->getLatestRevisionId($nid);
      /** @var \Drupal\node\NodeInterface $latest_revision */
      $latest_revision = $storage->loadRevision($latest_revision_id);

      $this->assertSame(1, EntityStorage::getMatchingEntityCount('node', 'va_form', $conditions));
      $this->assertSame('Forward Draft Test Form - Updated', $default_revision->get('field_va_form_title')->value);
      $this->assertSame('published', $default_revision->get('moderation_state')->value);
      $this->assertSame($default_revision->get('field_va_form_title')->value, $latest_revision->get('field_va_form_title')->value);
      $this->assertTrue($default_revision->get('field_va_form_tool_url')->isEmpty());
      $this->assertSame($tool_url, $latest_revision->get('field_va_form_tool_url')->first()->getValue()['uri']);
      $this->assertSame('draft', $latest_revision->get('moderation_state')->value);
      $this->assertFalse($latest_revision->isDefaultRevision());
      $this->assertNotSame($default_revision->getRevisionId(), $latest_revision_id);
      $this->assertNotSame($previous_latest_revision_id, $latest_revision_id);
      $this->assertNotSame($published_default_revision_id, (int) $default_revision->getRevisionId());
    }
    finally {
      EntityStorage::deleteMatchingEntities('node', 'va_form', $conditions);
      Migrator::removeMigrationMapping($migration_id, (string) self::FORWARD_DRAFT_ROW_ID);
    }
  }

}
