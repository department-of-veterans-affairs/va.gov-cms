<?php

namespace tests\phpunit\Content;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinksValidator;
use Tests\Support\Traits\ValidatorTestTrait;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinksValidator
 */
class PreventAbsoluteCmsLinksValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventAbsoluteCmsLinksValidator();
    $this->prepareValidator($validator, FALSE);
    $validator->addViolation(3, '99 :things on the wall, 99 :things, take one down, pass it around, 103 :things on the wall', [
      ':things' => 'violations of section 508',
    ]);
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
   * @covers ::validate
   * @covers ::validateText
   * @covers ::validateHtml
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
    $validator = new PreventAbsoluteCmsLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventAbsoluteCmsLinks());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider() {
    return [
      [
        TRUE,
        'Normal strings should not cause any issues.',
      ],
      [
        FALSE,
        'Random links to staging.cms.va.gov or tugboat.vfs.va.gov should not cause issues.',
      ],
      [
        TRUE,
        'text_long entries without domain names should not cause issues',
        'text_long',
      ],
      [
        FALSE,
        'text_long plain_text entries containing domain names like staging.cms.va.gov should trigger an error',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'text_long plain_text entries containing domain names like preview-prod.vfs.va.gov should trigger an error',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'URLs in rich text <a href="https://staging.cms.va.gov/">should trigger the validator</a>',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'URLs in rich text <a href="https://preview-prod.vfs.va.gov/">should trigger the validator</a>',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'for the love of prod and all that is holy, my <a href="https://staging.cms.va.gov">link</a> is <a href="https://test.dev.cms.va.gov/">broken</a>',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        'URLs in rich text <a href="https://va.gov/">should trigger the validator</a> only in a URL; https://tugboat.vfs.va.gov/ should not.',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
