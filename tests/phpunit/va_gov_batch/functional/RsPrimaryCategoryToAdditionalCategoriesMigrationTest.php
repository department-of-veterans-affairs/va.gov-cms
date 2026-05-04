<?php

namespace tests\phpunit\va_gov_batch\functional;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\va_gov_batch\cbo_scripts\RsPrimaryCategoryToAdditionalCategoriesMigration;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test for RsPrimaryCategoryToAdditionalCategoriesMigration.
 *
 * @group functional
 * @group va_gov_batch
 * @group all
 */
class RsPrimaryCategoryToAdditionalCategoriesMigrationTest extends VaGovExistingSiteBase {

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
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->markTestSkipped('Skipping RsPrimaryCategoryToAdditionalCategoriesMigrationTest. ExistingSiteBase is not supported tests are exhausting memory/resources and this one is not critical enough to run as it is a single use script.');

    parent::setUp();
    $vocab = Vocabulary::load('lc_categories');
    if (!$vocab) {
      $vocab = Vocabulary::create([
        'vid' => 'lc_categories',
        'name' => 'Resources and support Categories',
      ]);
      $vocab->save();
    }
  }

  /**
   * Creates an lc_categories term with a unique name.
   */
  protected function createLcCategoryTerm(string $suffix = ''): Term {
    $name = 'RsPrimaryToAdditional Test ' . $suffix . time() . random_int(1000, 9999);
    $term = Term::create([
      'vid' => 'lc_categories',
      'name' => $name,
    ]);
    $term->save();
    return $term;
  }

  /**
   * Test copying primary into empty additional categories.
   */
  public function testCopyPrimaryToAdditionalWhenAdditionalEmpty() {
    $primary = $this->createLcCategoryTerm('primary');

    $node = Node::create([
      'type' => 'q_a',
      'title' => 'Test Q&A primary to additional',
      'field_primary_category' => [['target_id' => $primary->id()]],
      'moderation_state' => 'draft',
    ]);
    $node->save();

    $this->assertTrue($node->get('field_other_categories')->isEmpty());

    $migration = \Drupal::classResolver(RsPrimaryCategoryToAdditionalCategoriesMigration::class);
    $sandbox = [];
    $result = $migration->processOne('test_key', $node->id(), $sandbox);
    $this->assertStringContainsString('processed successfully', $result, $result);

    $node = Node::load($node->id());
    $additional = $node->get('field_other_categories')->referencedEntities();
    $this->assertCount(1, $additional);
    $this->assertEquals((int) $primary->id(), (int) $additional[0]->id());

    $primary_ref = $node->get('field_primary_category')->referencedEntities();
    $this->assertCount(1, $primary_ref);
    $this->assertEquals((int) $primary->id(), (int) $primary_ref[0]->id());
  }

  /**
   * Test idempotency when primary is already listed in additional.
   */
  public function testNoOpWhenPrimaryAlreadyInAdditional() {
    $term = $this->createLcCategoryTerm('both');

    $node = Node::create([
      'type' => 'q_a',
      'title' => 'Test Q&A duplicate path',
      'field_primary_category' => [['target_id' => $term->id()]],
      'field_other_categories' => [
        ['target_id' => $term->id()],
      ],
      'moderation_state' => 'draft',
    ]);
    $node->save();

    $before_vid = $node->getRevisionId();

    $migration = \Drupal::classResolver(RsPrimaryCategoryToAdditionalCategoriesMigration::class);
    $sandbox = [];
    $result = $migration->processOne('test_key', $node->id(), $sandbox);

    $this->assertStringContainsString('already in additional categories', $result, $result);

    $node = Node::load($node->id());
    $this->assertEquals($before_vid, $node->getRevisionId(), 'No new revision when there is nothing to add.');
    $additional = $node->get('field_other_categories')->referencedEntities();
    $this->assertCount(1, $additional);
  }

  /**
   * Test failure when additional categories are at cardinality without primary.
   */
  public function testCardinalityLimitBlocksCopy() {
    $terms = [];
    for ($i = 0; $i < 7; $i++) {
      $terms[] = $this->createLcCategoryTerm('card' . $i);
    }
    $primary = $terms[6];
    $six_others = array_slice($terms, 0, 6);

    $other_values = [];
    foreach ($six_others as $t) {
      $other_values[] = ['target_id' => $t->id()];
    }

    $node = Node::create([
      'type' => 'q_a',
      'title' => 'Test Q&A cardinality full',
      'field_primary_category' => [['target_id' => $primary->id()]],
      'field_other_categories' => $other_values,
      'moderation_state' => 'draft',
    ]);
    $node->save();

    $migration = \Drupal::classResolver(RsPrimaryCategoryToAdditionalCategoriesMigration::class);
    $sandbox = [];
    $result = $migration->processOne('test_key', $node->id(), $sandbox);

    $this->assertStringContainsString('cardinality limit', $result, $result);

    $node = Node::load($node->id());
    $this->assertCount(6, $node->get('field_other_categories')->referencedEntities());
    $additional_tids = array_map(static function ($entity) {
      return (int) $entity->id();
    }, $node->get('field_other_categories')->referencedEntities());
    $this->assertNotContains((int) $primary->id(), $additional_tids);
  }

}
