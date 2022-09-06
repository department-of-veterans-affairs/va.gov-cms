<?php

namespace tests\phpunit\Content;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator;
use Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
 */
class PreventProtocolRelativeLinksValidatorTest extends UnitTestCase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventProtocolRelativeLinksValidator();
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
    $validator = new PreventProtocolRelativeLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventProtocolRelativeLinks());
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
        'However, string_long text with //something.like a protocol-relative URL should fail validation.',
      ],
      [
        TRUE,
        'Normal text_long text should not fail validation.',
        'text_long',
      ],
      [
        FALSE,
        'Something with a //pattern.like.this looks like it contains a protocol-relative URL.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="//www.example.org">protocol-relative link</a> inside a tag and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="//Users/bart/Downloads/You%20may%20be%20eligible%20for%20SNAP%20(1).pdf">protocol-relative link to a file</a> inside a tag and should fail.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        'This <a href="///www.example.org">protocol-relative link</a> starts with three slashes and is handled by a separate test.',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'This <a href="///www.example.org">triple-slash URL</a> should not trigger validation, while this <a href="//www.example.org">protocol-relative URL</a> should.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '<a href="mailto:rhaenys@targaryen.biz.co.uk">Mailto links</> should definitely not trigger the validator.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '//Protocol.relative.links outside of <a href="https://www.example.org/">anchor tags</a> should not trigger the validator.',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
