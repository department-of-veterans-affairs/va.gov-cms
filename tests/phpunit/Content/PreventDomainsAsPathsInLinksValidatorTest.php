<?php

namespace tests\phpunit\Content;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinksValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinksValidator
 */
class PreventDomainsAsPathsInLinksValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventDomainsAsPathsInLinksValidator();
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
   * @param string $fieldType
   *   The type of the text field, e.g. 'text_long' or 'string_long'.
   * @param string $format
   *   An optional format, like 'plain_text' or 'rich_text'.
   *
   * @covers validate
   * @covers validateText
   * @covers validateHtml
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $testString, string $fieldType = 'string_long', string $format = NULL) {
    $value = [
      'value' => $testString,
    ];
    if ($fieldType !== 'string_long') {
      $value['format'] = $format;
    }
    $items = $this->getFieldItemList([
      $this->getFieldItem($value, $fieldType),
    ]);
    $validator = new PreventDomainsAsPathsInLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventDomainsAsPathsInLinks());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider() {
    return [
      [
        TRUE,
        'Normal string_long text should not fail validation.',
      ],
      [
        TRUE,
        'Normal string_long text with the occasional // or / scattered throughout should not // fail validation.',
      ],
      [
        FALSE,
        'However, string_long text with a path that looks like a /www.navy.mil URL should fail validation.',
      ],
      [
        FALSE,
        'And string_long text with a path that looks like a /www.navy.mil/BUILD.txt URL should fail validation.',
      ],
      [
        TRUE,
        'But string_long text with a URL like https://www.navy.mil/BUILD.txt should pass validation.',
      ],
      [
        TRUE,
        'But string_long text with a relative URL like /some-path/another-path/www.navy.mil/BUILD.txt should pass validation.',
      ],
      [
        TRUE,
        'Normal text_long text should not fail validation.',
        'text_long',
      ],
      [
        FALSE,
        'Something with a /www.navy.mil URL looks like it contains a domain as a relative path and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'Something with a /www.navy.mil/ URL looks like it contains a URL as a relative path and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'Something with a /www.navy.mil/test.txt URL looks like it contains a URL as a relative path and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        'But something with a https://www.navy.mil/test.txt URL should pass.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="/www.navy.mil/test.txt">URL as a relative path</a> inside a tag and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        'This contains an <a href="https://www.navy.mil/test.txt">absolute URL</a> inside a tag and should not fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This <a href="http://www.navy.mil">absolute URL</a> should not trigger validation, while this <a href="/www.navy.mil/test">relative URL</a> should.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        'This <a href="/test-path/www.navy.mil">relative URL absolute URL</a> should not trigger validation either.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '/www.navy.mil outside of <a href="https://www.navy.mil/">anchor tags</a> should not trigger the validator.',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
