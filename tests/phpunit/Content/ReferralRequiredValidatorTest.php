<?php

namespace Tests\Content;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_vamc\Plugin\Validation\Constraint\ReferralRequired;
use Drupal\va_gov_vamc\Plugin\Validation\Constraint\ReferralRequiredValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * Tests for ReferralRequiredValidator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass ReferralRequiredValidator
 */
class ReferralRequiredValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::validate().
   *
   * @param bool $willValidate
   *   TRUE if the test string should validate, FALSE otherwise.
   * @param string $referralRequired
   *   The value of the 'field_referral_required' field.
   *
   * @covers ::validate
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $referralRequired): void {
    $entity = $this->prophesize(NodeInterface::class);

    $referralRequireddField = $this->fieldItemListMock($referralRequired);
    $entity->get('field_referral_required')->willReturn($referralRequireddField);

    $value = $this->prophesize(FieldItemListInterface::class);
    $value->getEntity()->willReturn($entity->reveal());
    $fieldItem = $value->reveal();

    $validator = new ReferralRequiredValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($fieldItem, new ReferralRequired());
  }

  /**
   * Mocks a FieldItemListInterface object for 'field_referral_required'.
   *
   * @param string $value
   *   The value to set for the field.
   *
   * @return object
   *   A mock object representing the field item list.
   */
  public function fieldItemListMock(string $value): object {

    $refReqdField = new class {

      /**
       * Stub for isEmpty() method.
       *
       * @return bool
       *   Always returns FALSE to indicate the field is not empty.
       */
      public function isEmpty(): bool {
        return FALSE;
      }

    };
    $refReqdField->value = $value;

    return $refReqdField;
  }

  /**
   * Data provider.
   */
  public function validateDataProvider(): array {
    return [
      [
        FALSE,
        'not_applicable',
      ],
      [
        TRUE,
        '1',
      ],
      [
        TRUE,
        '0',
      ],
    ];
  }

}
