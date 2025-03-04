<?php

namespace tests\phpunit\Content;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\ValidNumeric;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\ValidNumericValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\ValidNumericValidator
 */
class ValidNumericValidatorTest extends VaGovUnitTestBase {

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
    $value = new \stdClass();
    $value->value = $testString;
    $values = [];
    $values[] = $value;
    $validator = new ValidNumericValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($values, new ValidNumeric());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider():array {
    return [
      [
        TRUE,
        '1234567',
      ],
      [
        FALSE,
        '1234notvalid',
      ],
      [
        TRUE,
        '0',
      ],
      [
        FALSE,
        'notvalid1234',
      ],
    ];
  }

}
