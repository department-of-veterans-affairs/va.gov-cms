<?php

namespace tests\phpunit\va_gov_form_builder\kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\va_gov_form_builder\Entity\Paragraph\CustomStepParagraph;

/**
 * Kernel tests for CustomStepParagraph.
 *
 * @group kernel
 * @group all
 * @coversDefaultClass \Drupal\va_gov_form_builder\Entity\Paragraph\CustomStepParagraph
 */
class CustomStepParagraphKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'paragraphs',
    'va_gov_form_builder',
    'node',
    'entity_reference_revisions',
    'options',
    'link',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Install required schemas.
    $this->installEntitySchema('paragraph');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    // Install required configurations.
    $this->installConfig(['field', 'paragraphs', 'node', 'system', 'user']);

    // Create paragraph types.
    $this->createParagraphType('digital_form');
    $this->createParagraphType('digital_form_introduction');
    $this->createParagraphType('digital_form_custom_step');
    $this->createParagraphType('digital_form_your_personal_info');
    $this->createParagraphType('digital_form_address');
    $this->createParagraphType('digital_form_phone_and_email');

    // Create field storage for the parent-child relationship.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_steps',
      'entity_type' => 'paragraph',
      'type' => 'entity_reference_revisions',
      'cardinality' => -1,
      'settings' => [
        'target_type' => 'paragraph',
      ],
    ]);
    $field_storage->save();

    // Create field instance for the parent-child relationship.
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'digital_form',
      'settings' => [
        'handler' => 'default:paragraph',
        'handler_settings' => [
          'target_bundles' => [
            'digital_form_introduction' => 'digital_form_introduction',
            'digital_form_custom_step' => 'digital_form_custom_step',
            'digital_form_your_personal_info' => 'digital_form_your_personal_info',
            'digital_form_address' => 'digital_form_address',
            'digital_form_phone_and_email' => 'digital_form_phone_and_email',
          ],
        ],
      ],
    ]);
    $field->save();

    // Create field storage for field_title.
    $field_title_storage = FieldStorageConfig::create([
      'field_name' => 'field_title',
      'entity_type' => 'paragraph',
      'type' => 'string',
      'cardinality' => 1,
    ]);
    $field_title_storage->save();

    // Create field instance for field_title.
    foreach ([
      'digital_form_introduction',
      'digital_form_custom_step',
      'digital_form_your_personal_info',
      'digital_form_address',
      'digital_form_phone_and_email',
    ] as $bundle) {
      $field_title = FieldConfig::create([
        'field_storage' => $field_title_storage,
        'bundle' => $bundle,
      ]);
      $field_title->save();
    }
  }

  /**
   * Create a paragraph type.
   *
   * @param string $id
   *   The machine name of the paragraph type.
   */
  protected function createParagraphType(string $id): void {
    $paragraph_type = ParagraphsType::create([
      'id' => $id,
      'label' => $id,
    ]);
    $paragraph_type->save();
  }

  /**
   * Test that getFieldEntities filters out standard steps.
   *
   * @covers ::getFieldEntities
   */
  public function testGetFieldEntitiesFiltersStandardSteps(): void {
    // Create a parent paragraph that will contain our steps.
    $parent = Paragraph::create([
      'type' => 'digital_form',
      'field_steps' => [],
    ]);
    $parent->save();

    // Create a mix of standard and custom steps.
    $standard_step = Paragraph::create([
      'type' => 'digital_form_your_personal_info',
      'parent_id' => $parent->id(),
      'parent_type' => 'paragraph',
      'parent_field_name' => 'field_steps',
      'field_title' => 'Standard Step',
    ]);
    $standard_step->save();

    $custom_step = Paragraph::create([
      'type' => 'digital_form_custom_step',
      'parent_id' => $parent->id(),
      'parent_type' => 'paragraph',
      'parent_field_name' => 'field_steps',
      'field_title' => 'Custom Step',
    ]);
    $custom_step->save();

    // Add the steps to the parent.
    $parent->get('field_steps')->appendItem($standard_step);
    $parent->get('field_steps')->appendItem($custom_step);
    $parent->save();

    // Create our test paragraph.
    $test_paragraph = CustomStepParagraph::create([
      'type' => 'digital_form_custom_step',
      'parent_id' => $parent->id(),
      'parent_type' => 'paragraph',
      'parent_field_name' => 'field_steps',
      'field_title' => 'Test Step',
    ]);
    $test_paragraph->save();

    // Add the test paragraph to the parent.
    $parent->get('field_steps')->appendItem($test_paragraph);
    $parent->save();

    // Get the field entities and verify only custom steps are returned.
    $field_entities = $test_paragraph->getFieldEntities();

    // Assert that we have exactly two custom steps (the original custom step
    // and our test paragraph).
    $this->assertCount(2, $field_entities, 'Two custom steps should be returned (the original custom step and our test paragraph).');

    // Assert that both returned entities are custom steps.
    foreach ($field_entities as $entity) {
      $this->assertEquals('digital_form_custom_step', $entity->bundle(), 'The returned entity should be a custom step type.');
      $this->assertContains($entity->id(), [$custom_step->id(), $test_paragraph->id()], 'The returned entity should be either the custom step or the test paragraph.');
    }

    // Assert that the standard step is not included.
    foreach ($field_entities as $entity) {
      $this->assertNotEquals($standard_step->id(), $entity->id(), 'Standard step should not be included in the results.');
      $this->assertNotEquals('digital_form_your_personal_info', $entity->bundle(), 'Standard step type should not be included in the results.');
    }
  }

  /**
   * Test that actions are properly initialized and accessible.
   *
   * @covers ::getActionCollection
   * @covers ::initializeActionCollection
   */
  public function testActionCollectionInitialization(): void {
    // Create a test paragraph.
    $paragraph = CustomStepParagraph::create([
      'type' => 'digital_form_custom_step',
    ]);
    $paragraph->save();

    // Get the action collection and verify it's initialized.
    $action_collection = $paragraph->getActionCollection();

    // Assert that the action collection exists.
    $this->assertNotNull($action_collection, 'Action collection should not be null.');

    // Assert that all required actions are present.
    $this->assertTrue($action_collection->has('moveup'), 'Action collection should have moveup action.');
    $this->assertTrue($action_collection->has('movedown'), 'Action collection should have movedown action.');
    $this->assertTrue($action_collection->has('delete'), 'Action collection should have delete action.');

    // Assert that the actions are of the correct type.
    $this->assertInstanceOf('Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface', $action_collection->get('moveup'), 'Moveup action should implement ActionInterface.');
    $this->assertInstanceOf('Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface', $action_collection->get('movedown'), 'Movedown action should implement ActionInterface.');
    $this->assertInstanceOf('Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface', $action_collection->get('delete'), 'Delete action should implement ActionInterface.');
  }

}
