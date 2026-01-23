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
   * @param string $refReqd
   *   The value of the 'field_referral_required' field.
   *
   * @covers ::validate
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $refReqd): void {
    $entity = $this->prophesize(NodeInterface::class);
    $refReqdField = new \stdClass();
    $refReqdField->value = $refReqd;
    $entity->field_referral_required = $refReqdField;

    $value = $this->prophesize(FieldItemListInterface::class);
    $value->getEntity()->willReturn($entity->reveal());
    $fieldItem = $value->reveal();

    $validator = new ReferralRequiredValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($fieldItem, new ReferralRequired());
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
