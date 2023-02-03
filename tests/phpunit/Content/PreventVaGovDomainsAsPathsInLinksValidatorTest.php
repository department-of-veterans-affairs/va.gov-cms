<?php

namespace tests\phpunit\Content;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinksValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinksValidator
 */
class PreventVaGovDomainsAsPathsInLinksValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::addViolation().
   *
   * @covers ::addViolation
   */
  public function testAddViolation() {
    $validator = new PreventVaGovDomainsAsPathsInLinksValidator();
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
    $validator = new PreventVaGovDomainsAsPathsInLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventVaGovDomainsAsPathsInLinks());
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
        'However, string_long text with a path that looks like a /www.va.gov URL should fail validation.',
      ],
      [
        FALSE,
        'And string_long text with a path that looks like a /www.va.gov/BUILD.txt URL should fail validation.',
      ],
      [
        TRUE,
        'But string_long text with a URL like https://www.va.gov/BUILD.txt should pass validation.',
      ],
      [
        TRUE,
        'But string_long text with a relative URL like /some-path/another-path/www.va.gov/BUILD.txt should pass validation.',
      ],
      [
        TRUE,
        'Normal text_long text should not fail validation.',
        'text_long',
      ],
      [
        FALSE,
        'Something with a /prod.cms.va.gov URL looks like it contains a VA.gov URL as a relative path and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'Something with a /prod.cms.va.gov/ URL looks like it contains a VA.gov URL as a relative path and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'Something with a /prod.cms.va.gov/test.txt URL looks like it contains a VA.gov URL as a relative path and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        'But something with a https://prod.cms.va.gov/test.txt URL should pass.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This contains a <a href="/prod.cms.va.gov/test.txt">VA.gov URL as a relative path</a> inside a tag and should fail.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        'This contains an <a href="https://prod.cms.va.gov/test.txt">absolute VA.gov URL</a> inside a tag and should not fail.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'This <a href="http://www.va.gov">absolute URL</a> should not trigger validation, while this <a href="/www.va.gov/test">relative URL</a> should.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        'This <a href="/test-path/www.va.gov">relative URL absolute URL</a> should not trigger validation either.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '/broken-urls.va.gov outside of <a href="https://www.va.gov/">anchor tags</a> should not trigger the validator.',
        'text_long',
        'rich_text',
      ],
    ];
  }

}
