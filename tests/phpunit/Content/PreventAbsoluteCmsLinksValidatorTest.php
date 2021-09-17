<?php

namespace tests\phpunit\Content;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinksValidator;
use Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinksValidator
 */
class PreventAbsoluteCmsLinksValidatorTest extends UnitTestCase {

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
        'my spoon is too big',
      ],
      [
        FALSE,
        'i am a staging.cms.va.gov banana',
      ],
      [
        TRUE,
        'my SPOON is too big',
        'text_long',
      ],
      [
        FALSE,
        'i am the staging.cms.va.gov queen of france',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'and <a href="staging.cms.va.gov">how</a>',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'tuesday is coming <a href="/staging.cms.va.gov">did you bring your coat</a>?',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'i live in a <a href="//staging.cms.va.gov">giant bucket</a>!',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'say, do you want to go see a <a href="http://staging.cms.va.gov">movie</a>?',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'i am feeling <a href="https://staging.cms.va.gov">fat and sassy</a>.',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'my <a href="https://staging.cms.va.gov">link</a> is <a href="https://prod.cms.va.gov/">broken</a>',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'for the love of prod and all that is holy, my <a href="https://staging.cms.va.gov">link</a> is <a href="https://test.dev.cms.va.gov/">broken</a>',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
