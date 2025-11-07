<?php

namespace Tests\Content;

use Drupal\va_gov_facilities\Plugin\Validation\Constraint\ClosedOperatingStatusConstraint;
use Drupal\va_gov_facilities\Plugin\Validation\Constraint\ClosedOperatingStatusConstraintValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_facilities\Plugin\Validation\Constraint\ClosedOperatingStatusConstraintValidator
 */
class ClosedOperatingStatusConstraintValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::validate().
   *
   * @param bool $willValidate
   *   TRUE if the test string should validate, otherwise FALSE.
   * @param string $testString
   *   Some test string to attempt to validate.
   *
   * @covers ::validate
   * @covers ::validateText
   * @covers ::validateHtml
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $testString) {
    $value = new class([['value' => $testString]]) {

      /**
       * The items being tested.
       *
       * @var array
       */
      private array $items;

      /**
       * Constructor.
       *
       * @param array $items
       *   The items.
       */
      public function __construct(array $items) {
        $this->items = $items;
      }

      /**
       * Get the value.
       *
       * @return array
       *   The items.
       */
      public function getValue(): array {
        return $this->items;
      }

    };

    $validator = new ClosedOperatingStatusConstraintValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($value, new ClosedOperatingStatusConstraint());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider(): array {
    return [
      [
        TRUE,
        'normal',
      ],
      [
        TRUE,
        'limited',
      ],
      [
        TRUE,
        'temporary_closure',
      ],
      [
        TRUE,
        'temporary_location',
      ],
      [
        TRUE,
        'virtual_care',
      ],
      [
        TRUE,
        'coming_soon',
      ],
      [
        TRUE,
        'notice',
      ],
      [
        FALSE,
        'closed',
      ],
    ];
  }

}
