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
   * @param string $fieldType
   *   The type of the text field, e.g. 'string'.
   *
   * @covers ::validate
   * @covers ::validateText
   * @covers ::validateHtml
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $testString, string $fieldType = 'string') {
    $values = [];
    $values[] = ['value' => $testString];
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
