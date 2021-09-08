<?php

namespace tests\phpunit\Content;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAdjacentLinks;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAdjacentLinksValidator;
use Traits\ValidatorTestTrait;

/**
 * A test to confirm the proper functioning of this validator.
 *
 * @group functional
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAdjacentLinksValidator
 */
class PreventAdjacentLinksValidatorTest extends UnitTestCase {

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
    $validator = new PreventAdjacentLinksValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($items, new PreventAdjacentLinks());
  }

  /**
   * Data provider.
   */
  public function validateDataProvider() {
    return [
      [
        TRUE,
        'Oh -- here and there, here and there -- what a day! Ever see such a glorious day?',
      ],
      [
        TRUE,
        'The water up there, remember? That <a href="/transparent">transparent</a>, <a href="/light">light green water</a>! It -- it felt different!',
      ],
      [
        TRUE,
        'The Grahams, the Lears, the Bunkers. <div>Then a portage through the Pastern\'s riding ring to<a href="/the">the</a> <a href="/lindleys">Lindleys</a> and the Hallorans and over<b> the ridge to the Gilmartins and Eric Hammar\'s. Then up <a href="/alewives">Alewives</a> <a href="/lane">Lane</a></em> to the Biswangers, then, uh, wait a</div> minute -- who\'s next -- I can\'t think-- I had it a minute ago, I-- who is it?',
      ],
      [
        FALSE,
        'Oh-oh, I haven\'t spotted that one in a long time... Common species. Found <a href="/everywhere">everywhere except</a><a href="/home">home in the nest</a>.',
      ],
      [
        TRUE,
        'Kevin Gilmartin, Jr., after my father. <div>My mother <a href="/says">says</a></div><a href="/a/lot">I\'ve got a lot</a> to live down.',
      ],
      [
        TRUE,
        'No. <div>You <div>see <div>if <a href="/empty-pool">you make believe hard enough</a>&nbsp;<a href="/empty-pool">that something\'s true -- then it is true -- for you</a></div></div></div>.',
      ],
      [
        TRUE,
        'Hey, this is mine! How\'d this thing get here? I <a href="/biswangers"><em>wheel</em></a> <a href="/party"><em>my kids</em></a> around in it. See that? That\'s where Ellen put her foot through and I mended it with plywood. This is my wagon, man!',
      ],
      [
        TRUE,
        'You must be crazy. Everybody\'s gone crazy today. <a href="/biswangers"><em>I\'ve just come from the Biswangers.</em></a><a href="/biswangers"><b>They snubbed me.<b></a><a href="/biswangers"><h3>Everyone at their party snubbed me -- they\'ve even got my hot dog wagon and they won\'t give it back!</h3></a>',
      ],
    ];
  }

}
