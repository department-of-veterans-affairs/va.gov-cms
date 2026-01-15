<?php

namespace tests\phpunit\va_gov_batch\functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\va_gov_batch\cbo_scripts\PublicationRsCategoriesToOutreachHubMigration;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test for PublicationRsCategoriesToOutreachHubMigration.
 *
 * @group functional
 * @group va_gov_batch
 * @group all
 */
class PublicationRsCategoriesToOutreachHubMigrationTest extends VaGovExistingSiteBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'taxonomy',
    'field',
    'text',
    'options',
    'content_moderation',
    'workflows',
    'va_gov_batch',
    'va_gov_resources_and_support',
    'va_gov_content_types',
  ];

  /**
   * The R&S Categories taxonomy vocabulary.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $rsCategoriesVocabulary;

  /**
   * The Outreach Hub taxonomy vocabulary.
   *
   * @var \Drupal\taxonomy\Entity\Vocabulary
   */
  protected $outreachHubVocabulary;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Load existing vocabularies (they should exist in the existing site).
    $this->rsCategoriesVocabulary = Vocabulary::load('lc_categories');
    $this->outreachHubVocabulary = Vocabulary::load('outreach_materials_topics');

    // If vocabularies don't exist, create them (for test environments).
    if (!$this->rsCategoriesVocabulary) {
      $this->rsCategoriesVocabulary = Vocabulary::create([
        'vid' => 'lc_categories',
        'name' => 'Resources and support Categories',
      ]);
      $this->rsCategoriesVocabulary->save();
    }

    if (!$this->outreachHubVocabulary) {
      $this->outreachHubVocabulary = Vocabulary::create([
        'vid' => 'outreach_materials_topics',
        'name' => 'Outreach Materials Topics',
      ]);
      $this->outreachHubVocabulary->save();
    }

    // Ensure field_outreach_materials_topics exists (destination field).
    // Source field (field_lc_categories) should already exist.
    $field_storage_outreach = FieldStorageConfig::loadByName('node', 'field_outreach_materials_topics');
    if (!$field_storage_outreach) {
      try {
        $field_storage_outreach = FieldStorageConfig::create([
          'field_name' => 'field_outreach_materials_topics',
          'entity_type' => 'node',
          'type' => 'entity_reference',
          'settings' => [
            'target_type' => 'taxonomy_term',
          ],
          'cardinality' => -1,
        ]);
        $field_storage_outreach->save();
      }
      catch (\Exception $e) {
        // Field might already exist, continue.
      }
    }

    $field_outreach = FieldConfig::loadByName('node', 'outreach_asset', 'field_outreach_materials_topics');
    if (!$field_outreach && $field_storage_outreach) {
      try {
        $field_outreach = FieldConfig::create([
          'field_storage' => $field_storage_outreach,
          'bundle' => 'outreach_asset',
          'settings' => [
            'handler' => 'default:taxonomy_term',
            'handler_settings' => [
              'target_bundles' => [
                'outreach_materials_topics' => 'outreach_materials_topics',
              ],
            ],
          ],
        ]);
        $field_outreach->save();
      }
      catch (\Exception $e) {
        // Field instance might already exist, continue.
      }
    }
  }

  /**
   * Test that migration copies terms from R&S Categories to Outreach Hub.
   */
  public function testMigrationCopiesTermsToOutreachHub() {
    // Create test taxonomy terms.
    // Use a unique name to avoid conflicts with existing terms.
    $unique_name = 'Test Category ' . time();
    $rs_term = Term::create([
      'vid' => 'lc_categories',
      'name' => $unique_name,
    ]);
    $rs_term->save();

    $outreach_term = Term::create([
      'vid' => 'outreach_materials_topics',
      'name' => $unique_name,
    ]);
    $outreach_term->save();

    // Create a test Publication node with R&S Categories term.
    $node = Node::create([
      'type' => 'outreach_asset',
      'title' => 'Test Publication',
      'field_lc_categories' => [['target_id' => $rs_term->id()]],
      'moderation_state' => 'draft',
    ]);
    $node->save();

    // Verify node starts without Outreach Hub term.
    $this->assertTrue($node->get('field_outreach_materials_topics')->isEmpty(), 'Node should not have Outreach Hub terms initially.');

    // Run the migration using class resolver (same way codit_batch_operations
    // does).
    $migration = \Drupal::classResolver(PublicationRsCategoriesToOutreachHubMigration::class);
    $items = $migration->gatherItemsToProcess();
    $this->assertNotEmpty($items, 'Migration should find nodes to process.');

    // Process the node.
    $sandbox = [];
    $result = $migration->processOne('test_key', $node->id(), $sandbox);
    if (!str_contains($result, 'processed successfully')) {
      // Get error details from batch operation log.
      $errors = $migration->getBatchOpLog()->getErrors();
      $error_message = $errors ?: $result;
      $this->fail("Migration failed: $error_message");
    }
    $this->assertStringContainsString('processed successfully', $result, 'Migration should process node successfully.');

    // Reload node and verify Outreach Hub term was added.
    $node = Node::load($node->id());
    $outreach_terms = $node->get('field_outreach_materials_topics')->referencedEntities();
    $this->assertCount(1, $outreach_terms, 'Node should have one Outreach Hub term after migration.');
    // Migration finds terms by name, so verify the name matches.
    $this->assertEquals($unique_name, $outreach_terms[0]->getName(), 'Outreach Hub term name should match.');

    // Verify R&S Categories term is still present.
    $rs_terms = $node->get('field_lc_categories')->referencedEntities();
    $this->assertCount(1, $rs_terms, 'R&S Categories term should still be present.');
  }

  /**
   * Test that migration handles nodes without matching terms gracefully.
   */
  public function testMigrationHandlesMissingTerms() {
    // Create R&S Categories term without matching Outreach Hub term.
    $rs_term = Term::create([
      'vid' => 'lc_categories',
      'name' => 'Unmatched Category',
    ]);
    $rs_term->save();

    // Create a test Publication node.
    $node = Node::create([
      'type' => 'outreach_asset',
      'title' => 'Test Publication Without Match',
      'field_lc_categories' => [['target_id' => $rs_term->id()]],
      'moderation_state' => 'draft',
    ]);
    $node->save();

    // Run the migration using class resolver.
    $migration = \Drupal::classResolver(PublicationRsCategoriesToOutreachHubMigration::class);
    $sandbox = [];
    $result = $migration->processOne('test_key', $node->id(), $sandbox);

    // Should return a message indicating no matching terms found.
    $this->assertStringContainsString('No matching destination terms found', $result, 'Migration should handle missing terms gracefully.');

    // Verify node still has no Outreach Hub terms.
    $node = Node::load($node->id());
    $this->assertTrue($node->get('field_outreach_materials_topics')->isEmpty(), 'Node should not have Outreach Hub terms when no match exists.');
  }

  /**
   * Test that migration preserves moderation state.
   */
  public function testMigrationPreservesModerationState() {
    // Create test taxonomy terms.
    $rs_term = Term::create([
      'vid' => 'lc_categories',
      'name' => 'Published Category',
    ]);
    $rs_term->save();

    $outreach_term = Term::create([
      'vid' => 'outreach_materials_topics',
      'name' => 'Published Category',
    ]);
    $outreach_term->save();

    // Create a published Publication node.
    $node = Node::create([
      'type' => 'outreach_asset',
      'title' => 'Published Publication',
      'field_lc_categories' => [['target_id' => $rs_term->id()]],
      'moderation_state' => 'published',
      'status' => 1,
    ]);
    $node->save();

    $original_moderation_state = $node->get('moderation_state')->value;

    // Run the migration using class resolver.
    $migration = \Drupal::classResolver(PublicationRsCategoriesToOutreachHubMigration::class);
    $sandbox = [];
    $migration->processOne('test_key', $node->id(), $sandbox);

    // Reload node and verify moderation state is preserved.
    $node = Node::load($node->id());
    $this->assertEquals($original_moderation_state, $node->get('moderation_state')->value, 'Moderation state should be preserved.');
  }

}
