<?php

use Drupal\va_gov_form_builder\Service\FieldValidator\UniquenessValidator;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the UniquessValidator service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Service\FieldValidator\UniquenessValidator
 */
class UniquenessValidatorTest extends VaGovExistingSiteBase {

  /**
   * UniquenessValidator service.
   *
   * @var \Drupal\va_gov_form_builder\Service\FieldValidator\UniquenessValidator
   */
  private $validatorService;

  /**
   * A unique VA form number.
   *
   * A string that we know will be unique among field_va_form_number
   * values on Digital Form nodes.
   *
   * @var string
   */
  private const UNIQUE_VA_FORM_NUMBER = 'unique_va_form_number_!@#$%^&*()';

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->validatorService = \Drupal::service('va_gov_form_builder.field_validator.uniqueness');
  }

  /**
   * Tests that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(UniquenessValidator::class, $this->validatorService);
  }

  /**
   * Tests the validate() method when $nid of current node IS NOT passed.
   *
   * @covers ::validate
   */
  public function testValidateWithoutCurrentNode() {
    // A Digital Form node with our unique VA form number
    // should not already exist.
    $isUnique = $this->validatorService->validate(
      'digital_form',
      'field_va_form_number',
      $this->UNIQUE_VA_FORM_NUMBER,
    );
    $this->assertTrue($isUnique);
  }

  /**
   * Tests the validate() method when $nid of current node IS passed.
   *
   * @covers ::validate
   */
  public function testValidateWithCurrentNode() {
    // Create a node with our unique VA form number.
    $createdNode = $this->createNode([
      'type' => 'digital_form',
      'field_va_form_number' => $this->UNIQUE_VA_FORM_NUMBER,
    ]);

    // Should be unique if we pass the $nid of the newly created node
    // (newly created node is excluded from the check).
    $isUnique = $this->validatorService->validate(
      'digital_form',
      'field_va_form_number',
      $this->UNIQUE_VA_FORM_NUMBER,
      $createdNode->nid->value,
    );
    $this->assertTrue($isUnique);

    // Should NOT be unique if we pass the $nid of a different node
    // (newly created node is included in the check).
    $isUnique = $this->validator_service->validate(
      'digital_form',
      'field_va_form_number',
      $this->UNIQUE_VA_FORM_NUMBER,
      // $nid = '1' to represent some other node.
      '1',
    );
    $this->assertFalse($isUnique);
  }

}
