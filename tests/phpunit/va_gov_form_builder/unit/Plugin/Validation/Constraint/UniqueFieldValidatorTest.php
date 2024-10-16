<?php

namespace tests\phpunit\va_gov_form_builder\unit\Plugin\Validation\Constraint;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueField;
use Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueFieldValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for the UniqueFieldValidator constraint validator.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueFieldValidator
 */
class UniqueFieldValidatorTest extends VaGovUnitTestBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The execution context mock.
   *
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  private $context;

  /**
   * The UniqueFieldValidator.
   *
   * @var \Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueFieldValidator
   */
  private $validator;

  /**
   * Creates a bare mock validation item.
   */
  private function createBareMockValidationItem() {
    return $this->createMock(FieldItemListInterface::class);
  }

  /**
   * Creates a mock validation item.
   *
   * @param bool $isNewEntity
   *   Should the returned mock represent a new entity?
   */
  private function createMockValidationItem($isNewEntity = TRUE) {
    $item = $this->createBareMockValidationItem();

    $entity = $this->createMock(ContentEntityInterface::class);
    $entity->method('isNew')
      ->willReturn($isNewEntity);

    $item->method('getEntity')
      ->willReturn($entity);

    $fieldDefinition = $this->createMock(FieldDefinitionInterface::class);
    $fieldDefinition->method('getLabel')
      ->willReturn('Test Field Label');

    $item->method('getFieldDefinition')
      ->willReturn($fieldDefinition);

    $item->value = 'test_value';

    return $item;
  }

  /**
   * Creates a mock validation item representing a new entity.
   */
  private function createMockValidationItemNewEntity() {
    return $this->createMockValidationItem(TRUE);
  }

  /**
   * Creates a mock validation item representing an existing entity.
   */
  private function createMockValidationItemExistingEntity() {
    return $this->createMockValidationItem(FALSE);
  }

  /**
   * Creates a bare mock entityTypeManager.
   */
  private function createBareMockEntityTypeManager() {
    return $this->createMock(EntityTypeManagerInterface::class);
  }

  /**
   * Creates a mock entityTypeManager.
   */
  private function createMockEntityTypeManager($isUniqueEntity = TRUE) {
    $entityTypeManager = $this->createBareMockEntityTypeManager();

    $query = $this->createMock(QueryInterface::class);
    $query->method('accessCheck')
      ->willReturnSelf();
    $query->method('condition')
      ->willReturnSelf();
    $query->method('execute')
      ->willReturn($isUniqueEntity ? [] : [1]);

    $nodeStorage = $this->createMock(EntityStorageInterface::class);
    $nodeStorage->method('getQuery')
      ->willReturn($query);

    // phpcs:disable
    //
    // Workaround.
    // Mocking \Drupal\node\Entity\NodeType::class causes error,
    // seemingly due to some references not being fully bootstrapped
    // in the context of a unit test.

    // $nodeType = $this->createMock(\Drupal\node\Entity\NodeType::class);
    // $nodeType->expects($this->any())
    //   ->method('label')
    //   ->willReturn('Test Bundle Label');
    //
    $nodeType = new class {
      public function label() {
        return 'Test Bundle Label';
      }
    };
    // phpcs:enable

    $nodeTypeStorage = $this->createMock(EntityStorageInterface::class);
    $nodeTypeStorage->method('load')
      ->willReturn($nodeType);

    $entityTypeManager->method('getStorage')
      ->willReturnMap([
        ['node_type', $nodeTypeStorage],
        ['node', $nodeStorage],
      ]);

    return $entityTypeManager;
  }

  /**
   * Creates a mock entityTypeManager representing a unique entity.
   */
  private function createMockEntityTypeManagerUniqueEntity() {
    return $this->createMockEntityTypeManager(TRUE);
  }

  /**
   * Creates a mock entityTypeManager representing a non-unique entity.
   */
  private function createMockEntityTypeManagerNonUniqueEntity() {
    return $this->createMockEntityTypeManager(FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->context = $this->createMock(ExecutionContextInterface::class);
    $this->constraint = new UniqueField();
  }

  /**
   * Tests the validator with an invalid parameter.
   *
   * @covers ::validate
   */
  public function testValidateInvalidParameter() {
    $entityTypeManager = $this->createBareMockEntityTypeManager();
    $validator = new UniqueFieldValidator($entityTypeManager);

    // Expect an exception.
    $this->expectException(\InvalidArgumentException::class);

    $validator->initialize($this->context);
    $validator->validate(new \stdClass(), $this->constraint);
  }

  /**
   * Tests the validator with a unique entity.
   *
   * @covers ::validate
   */
  public function testValidateUnique() {
    $entityTypeManager = $this->createMockEntityTypeManagerUniqueEntity();
    $validator = new UniqueFieldValidator($entityTypeManager);
    $validationItem = $this->createMockValidationItem();

    // Do not expect a violation.
    $this->context->expects($this->never())
      ->method('buildViolation');

    $validator->initialize($this->context);
    $validator->validate($validationItem, $this->constraint);
  }

  /**
   * Tests the validator with a non-unique entity.
   *
   * @cover ::validate
   */
  public function testValidateNonUnique() {
    $entityTypeManager = $this->createMockEntityTypeManagerNonUniqueEntity();
    $validator = new UniqueFieldValidator($entityTypeManager);
    $validationItem = $this->createMockValidationItem();

    // Expect a violation.
    $this->context->expects($this->once())
      ->method('buildViolation')
      ->with(
        $this->constraint->message,
        $this->arrayHasKey(':bundle_label')
      );

    $validator->initialize($this->context);
    $validator->validate($validationItem, $this->constraint);
  }

  /**
   * Tests the validator in the context of a new entity.
   */
  public function testValidateNewEntity() {
    $entityTypeManager = $this->createMockEntityTypeManager();
    $validator = new UniqueFieldValidator($entityTypeManager);
    $validationItem = $this->createMockValidationItemNewEntity();

    // Expect the query to have two conditions.
    $query = $entityTypeManager->getStorage('node')->getQuery();
    $query->expects($this->exactly(2))
      ->method('condition');

    $validator->initialize($this->context);
    $validator->validate($validationItem, $this->constraint);
  }

  /**
   * Tests the validator in the context of an existing entity.
   */
  public function testValidateEntityEdit() {
    $entityTypeManager = $this->createMockEntityTypeManager();
    $validator = new UniqueFieldValidator($entityTypeManager);
    $validationItem = $this->createMockValidationItemExistingEntity();

    // Expect the query to have three conditions.
    $query = $entityTypeManager->getStorage('node')->getQuery();
    $query->expects($this->exactly(3))
      ->method('condition');

    $validator->initialize($this->context);
    $validator->validate($validationItem, $this->constraint);
  }

}
