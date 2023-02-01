<?php

namespace tests\phpunit\Content;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinksValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
 */
class PreventAbsoluteUrlsAsPathsInLinksValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventAbsoluteUrlsAsPathsInLinksValidator();
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
    $validator = new PreventAbsoluteUrlsAsPathsInLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventAbsoluteUrlsAsPathsInLinks());
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
        'Normal string_long text with the occasional // scattered throughout should not // fail validation.',
      ],
      [
        FALSE,
        'However, string_long text with /http://something.like a path that consists of an absolute URL should fail validation.',
      ],
      [
        TRUE,
        'Normal text_long text should not fail validation.',
        'text_long',
      ],
      [
        FALSE,
        'Something with a /http://pattern.like.this looks like it contains an absolute URL preceded by a slash and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="/https://www.example.org">path consisting of an absolute URL</a> inside a tag and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        '/http://paths-that-look-like-absolute-urls outside of <a href="https://www.example.org/">anchor tags</a> should not trigger the validator.',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
