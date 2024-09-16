<?php

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\va_gov_form_builder\Service\FieldValidator\UniquenessValidator;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the UniquenessValidator service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Service\FieldValidator\UniquenessValidator
 */
class UniquenessValidatorTest extends VaGovUnitTestBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Query interface.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  /**
   * The UniquessValidator service.
   *
   * @var \Drupal\va_gov_form_builder\Service\FieldValidator\UniquenessValidator
   */
  protected $validator;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    $this->query = $this->createMock(QueryInterface::class);
    $this->query->expects($this->once())
      ->method('accessCheck')
      ->willReturn($this->query);
    $this->query->expects($this->exactly(2))
      ->method('condition')
      ->willReturn($this->query);

    $this->validator = new UniquenessValidator($this->entityTypeManager);
  }

  /**
   * Tests the validate() method when no matching node exists.
   *
   * @covers ::validate
   */
  public function testFieldIsUnique() {
    $nodeStorage = $this->createMock(EntityStorageInterface::class);

    // Empty result - no matching node exists.
    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn([]);

    $nodeStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($this->query);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($nodeStorage);

    $result = $this->validator->validate('some_content_type', 'some_field', 'some_value');
    $this->assertTrue($result);
  }

  /**
   * Tests the validate() method when a matching node exists.
   *
   * @covers ::validate
   */
  public function testFieldIsNotUnique() {
    $nodeStorage = $this->createMock(EntityStorageInterface::class);

    // Return something other than empty - matching node exists.
    $this->query->expects($this->once())
      ->method('execute')
      ->willReturn([1]);

    $nodeStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($this->query);

    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($nodeStorage);

    $result = $this->validator->validate('some_content_type', 'some_field', 'some_value');
    $this->assertFalse($result);
  }

}
