<?php

namespace tests\phpunit\Content;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlLinksValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
 */
class PreventPreviewUrlLinksValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventPreviewUrlLinksValidator();
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
    $validator = new PreventPreviewUrlLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventPreviewUrlLinks());
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
        'However, string_long text with http://preview-staging.vfs.va.gov/path/to/content that consists of an Preview URL should fail validation.',
      ],
      [
        FALSE,
        'However, string_long text with http://preview-prod.vfs.va.gov/path/to/content that consists of an Preview URL should fail validation.',
      ],
      [
        TRUE,
        'Normal text_long text should not fail validation.',
        'text_long',
      ],
      [
        FALSE,
        'Something with a http://preview-staging.vfs.va.gov/path/to/content looks like it contains an Preview URL and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'Something with a https://preview-prod.vfs.va.gov/path/to/content looks like it contains an Preview URL and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="https://preview-staging.vfs.va.gov/path/to/content">path consisting of an Preview URL</a> inside a tag and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="https://preview-prod.vfs.va.gov/path/to/content">path consisting of an Preview URL</a> inside a tag and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        'http://preview-staging.vfs.va.gov/path/to/content outside of <a href="https://www.example.org/">anchor tags</a> should not trigger the validator.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        'http://preview-prod.vfs.va.gov/path/to/content outside of <a href="https://www.example.org/">anchor tags</a> should not trigger the validator.',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
