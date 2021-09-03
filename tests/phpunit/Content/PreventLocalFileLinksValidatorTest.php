<?php

namespace tests\phpunit\Content;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventLocalFileLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventLocalFileLinksValidator;
use Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventLocalFileLinksValidator
 */
class PreventLocalFileLinksValidatorTest extends UnitTestCase {

  use ValidatorTestTrait;

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
      'format' => 'rich_text',
    ];
    $items = $this->getFieldItemList([
      $this->getFieldItem($value, 'text_long'),
    ]);
    $validator = new PreventLocalFileLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventLocalFileLinks());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider() {
    return [
      [
        TRUE,
        'My life fades... the vision dims. All that remains are memories. I remember a time of chaos... ruined dreams... this wasted land.',
      ],
      [
        TRUE,
        'But most of all, I remember ///U:/My%20Documents/COVID-19/Vaccine/Letter%20to%20Veterans%202021.07.09.pdf – the man we called Max.',
      ],
      [
        TRUE,
        'Four days I was up here. Me and the C:/snakes – playing A:\mah-jong, taking tea – watching, thinking how was I going to get in and get the d:\gas?',
      ],
      [
        FALSE,
        'Round and round, attack, attack – like <a href="///U:/My%20Documents/COVID-19/Vaccine/Letter%20to%20Veterans%202021.07.09.pdf">angry ants</a>, mad with the smell of gasoline.',
      ],
      [
        FALSE,
        'I reckon you got a <a href="///Users/mel.gibson/My%20Documents/COVID-19/Vaccine/Letter%20to%20Veterans%202021.07.09.pdf">bargain</a>, don\'t you?',
      ],
      [
        FALSE,
        'Onward! Bring me the fuel. For the glory of <a href="///C:/Users/MYNAME/AppData/Local/Microsoft/Windows/INetCache/Content.Outlook/AUJLV0UN/">Humungus</a>.',
      ],
      [
        FALSE,
        'You sent them out this morning to find a vehicle. A <a href="///usr/local/share/gtk-doc/html/libidn2/api-index-full.html">rig</a> big enough to haul that fat tank of gas. What a puny plan! Look around you. This is the Valley of Death.',
      ],
    ];
  }

}
