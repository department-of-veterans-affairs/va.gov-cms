<?php

namespace Tests\va_gov_form_builder\unit\Traits;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityConstraintViolationList;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_form_builder\Traits\EntityReferenceRevisionsOperations;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Tests the EntityReferenceRevisionsOperations trait.
 *
 * @group va_gov_form_builder
 * @group unit
 */
class EntityReferenceRevisionsOperationsTest extends UnitTestCase {

  /**
   * A class that uses the trait for testing.
   *
   * @var object
   */
  protected $traitObject;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->traitObject = new class() {
      use EntityReferenceRevisionsOperations;
    };
  }

  /**
   * Tests the recursiveEntityReferenceRevisionValidator method.
   */
  public function testRecursiveEntityReferenceRevisionValidator() {
    // Create a mock entity with no violations.
    $entity = $this->prophesize(ContentEntityInterface::class);
    $entity->validate()->willReturn(new EntityConstraintViolationList($entity->reveal(), []));

    // Create a mock field definition for a non-entity reference field.
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $field_storage = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage->isBaseField()->willReturn(FALSE);
    $field_definition->getFieldStorageDefinition()->willReturn($field_storage->reveal());
    $field_definition->getType()->willReturn('string');

    // Create a mock field item list.
    $field = $this->prophesize(FieldItemListInterface::class);
    $field->getFieldDefinition()->willReturn($field_definition->reveal());

    // Set up the entity to return our mock field.
    $entity->getFields()->willReturn(['test_field' => $field->reveal()]);

    $violations = new EntityConstraintViolationList($entity->reveal(), []);
    $result = $this->traitObject->recursiveEntityReferenceRevisionValidator($entity->reveal(), $violations);

    $this->assertInstanceOf(EntityConstraintViolationList::class, $result);
    $this->assertCount(0, $result);
  }

  /**
   * Tests recursiveEntityReferenceRevisionValidator with nested entities.
   */
  public function testRecursiveEntityReferenceRevisionValidatorWithNestedEntities() {
    // Create a mock deeply nested entity with no violations.
    $deeply_nested_entity = $this->prophesize(ContentEntityInterface::class);
    $deeply_nested_entity->validate()->willReturn(new EntityConstraintViolationList($deeply_nested_entity->reveal(), []));
    $deeply_nested_entity->getFields()->willReturn([]);

    // Create a mock nested entity with no violations that references the deeply
    // nested entity.
    $nested_entity = $this->prophesize(ContentEntityInterface::class);
    $nested_entity->validate()->willReturn(new EntityConstraintViolationList($nested_entity->reveal(), []));

    // Create a mock field definition for an entity reference revisions field.
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $field_storage = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage->isBaseField()->willReturn(FALSE);
    $field_definition->getFieldStorageDefinition()->willReturn($field_storage->reveal());
    $field_definition->getType()->willReturn('entity_reference_revisions');

    // Create a mock field item list that returns our deeply nested entity.
    $nested_field = $this->prophesize(EntityReferenceFieldItemListInterface::class);
    $nested_field->getFieldDefinition()->willReturn($field_definition->reveal());
    $nested_field->referencedEntities()->willReturn([$deeply_nested_entity->reveal()]);

    // Set up the nested entity to return our mock field.
    $nested_entity->getFields()->willReturn(['nested_field' => $nested_field->reveal()]);

    // Create a mock field item list that returns our nested entity.
    $field = $this->prophesize(EntityReferenceFieldItemListInterface::class);
    $field->getFieldDefinition()->willReturn($field_definition->reveal());
    $field->referencedEntities()->willReturn([$nested_entity->reveal()]);

    // Create a mock parent entity with no violations.
    $parent_entity = $this->prophesize(ContentEntityInterface::class);
    $parent_entity->validate()->willReturn(new EntityConstraintViolationList($parent_entity->reveal(), []));
    $parent_entity->getFields()->willReturn(['test_field' => $field->reveal()]);

    $violations = new EntityConstraintViolationList($parent_entity->reveal(), []);
    $result = $this->traitObject->recursiveEntityReferenceRevisionValidator($parent_entity->reveal(), $violations);

    $this->assertInstanceOf(EntityConstraintViolationList::class, $result);
    $this->assertCount(0, $result);
  }

  /**
   * Tests the recursiveEntityReferenceRevisionValidator method with violations.
   */
  public function testRecursiveEntityReferenceRevisionValidatorWithViolations() {
    // Create a mock entity with violations.
    $entity = $this->prophesize(ContentEntityInterface::class);
    $violation = $this->prophesize(ConstraintViolation::class)->reveal();
    $violation_list = new EntityConstraintViolationList($entity->reveal(), [$violation]);
    $entity->validate()->willReturn($violation_list);

    // Create a mock field definition for a non-entity reference field.
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $field_storage = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage->isBaseField()->willReturn(FALSE);
    $field_definition->getFieldStorageDefinition()->willReturn($field_storage->reveal());
    $field_definition->getType()->willReturn('string');

    // Create a mock field item list.
    $field = $this->prophesize(FieldItemListInterface::class);
    $field->getFieldDefinition()->willReturn($field_definition->reveal());

    // Set up the entity to return our mock field.
    $entity->getFields()->willReturn(['test_field' => $field->reveal()]);

    $violations = new EntityConstraintViolationList($entity->reveal(), []);
    $result = $this->traitObject->recursiveEntityReferenceRevisionValidator($entity->reveal(), $violations);

    $this->assertInstanceOf(EntityConstraintViolationList::class, $result);
    $this->assertCount(1, $result);
  }

  /**
   * Tests recursiveEntityReferenceRevisionValidator method with base fields.
   */
  public function testRecursiveEntityReferenceRevisionValidatorWithBaseFields() {
    // Create a mock entity with no violations.
    $entity = $this->prophesize(ContentEntityInterface::class);
    $entity->validate()->willReturn(new EntityConstraintViolationList($entity->reveal(), []));

    // Create a mock field definition for a base field.
    $field_definition = $this->prophesize(FieldDefinitionInterface::class);
    $field_storage = $this->prophesize(FieldStorageDefinitionInterface::class);
    $field_storage->isBaseField()->willReturn(TRUE);
    $field_definition->getFieldStorageDefinition()->willReturn($field_storage->reveal());

    // Create a mock field item list.
    $field = $this->prophesize(FieldItemListInterface::class);
    $field->getFieldDefinition()->willReturn($field_definition->reveal());

    // Set up the entity to return our mock field.
    $entity->getFields()->willReturn(['test_field' => $field->reveal()]);

    $violations = new EntityConstraintViolationList($entity->reveal(), []);
    $result = $this->traitObject->recursiveEntityReferenceRevisionValidator($entity->reveal(), $violations);

    $this->assertInstanceOf(EntityConstraintViolationList::class, $result);
    $this->assertCount(0, $result);
  }

}
