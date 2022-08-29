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
        'As the first century of the Targaryen dynasty came to a close The health of the Old King, Jaehaerys, was failing.',
      ],
      [
        TRUE,
        'In those days, //House Targaryen stood at the height of its strength with ten adult dragons under its yoke.',
      ],
      [
        TRUE,
        'No power in the world could stand against it.',
        'text_long',
      ],
      [
        FALSE,
        'King Jaehaerys reigned over nearly 60 years of peace and prosperity but tragedy had claimed both his sons, //Leaving.his succession in doubt.',
        'text_long',
        'plain_text',
      ],
      [
        FALSE,
        'So, in the year 101 The Old King called a <a href="//www.example.org">Great Council</a> to choose an heir.',
        'text_long',
        'plain_text',
      ],
      [
        TRUE,
        'Over a thousand lords made the journey to <a href="///www.example.org">Harrenhal</a>.',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'Fourteen succession claims were heard... but only two were truly <a href="///www.example.org">considered</a>: Princess Rhaenys Targaryen, the King\'s eldest descendant; and her younger cousin, <a href="//www.example.org">Prince Viserys Targaryen</a>, the King\'s eldest <i>male</i> descendant..',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '<a href="mailto:rhaenys@targaryen.biz.co.uk">Rhaenys</>, a woman, would not inherit the Iron Throne.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '//The lords instead chose <a href="gopher://viserys/">Viserys</a>... my father.',
        'text_long',
        'rich_text',
      ],
      [
        FALSE,
        'Jaehaerys called the <a href="//www.example.org/">Great Council</a> to prevent a war being fought over his succession.',
        'text_long',
        'rich_text',
      ],
      [
        TRUE,
        '// For //he knew //the cold truth:// the only thing that could tear down the House of the Dragon was itself.',
        'text_long',
        'plain_text',
      ],
    ];
  }

}
