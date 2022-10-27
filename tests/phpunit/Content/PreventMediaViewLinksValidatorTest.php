<?php

namespace tests\phpunit\Content;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventMediaViewLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventMediaViewLinksValidator;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventMediaViewLinksValidator
 */
class PreventMediaViewLinksValidatorTest extends UnitTestCase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventMediaViewLinksValidator();
    $this->prepareValidator($validator, FALSE);
    $validator->addViolation(3, 'Test violation');
  }

  /**
   * Test ::validate().
   *
   * @param bool $willValidate
   *   TRUE if the test string should validate, otherwise FALSE.
   * @param string $testString
   *   Some test string to attempt to validate.
   *
   * @covers validate
   * @covers validateText
   * @covers validateHtml
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $testString) {
    $value = [
      'value' => $testString,
    ];
    $value['format'] = 'rich_text';
    $items = $this->getFieldItemList([
      $this->getFieldItem($value, 'rich_text'),
    ]);
    $validator = new PreventMediaViewLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventMediaViewLinks());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider() {
    return [
      [
        TRUE,
        'This <a href="https://www.example.org/media/5">media view link</a> is not a relative URL and should pass.',
      ],
      [
        FALSE,
        'This contains a <a href="/media/5">media view link</a> and should fail.',
      ],
      [
        TRUE,
        'This <a href="///media/5">media view link</a> starts with three slashes and is handled by a separate test.',
      ],
    ];
  }

}
