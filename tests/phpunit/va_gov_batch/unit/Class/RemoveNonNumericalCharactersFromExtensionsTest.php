<?php

namespace tests\phpunit\va_gov_batch\unit\Class;

use Drupal\va_gov_batch\cbo_scripts\RemoveNonNumericalCharactersFromExtensions;
use PHPUnit\Framework\TestCase;

/**
 * Test for RemoveNonNumericalCharactersFromExtensions::replaceNonNumerals.
 *
 * @group va_gov_batch
 */
class RemoveNonNumericalCharactersFromExtensionsTest extends TestCase {

  /**
   * Test the extractPhoneAndExtension method.
   *
   * @dataProvider extensionsProvider
   */
  public function testReplaceNonNumerals($extension, $expectedExtension) {
    $result = RemoveNonNumericalCharactersFromExtensions::replaceNonNumerals($extension);

    $this->assertSame($expectedExtension, $result);
  }

  /**
   * Data provider for testReplaceNonNumerals.
   */
  public function extensionsProvider() {
    return [
      'Test case 1: with commas' => [
        'extension' => ',,0',
        'expectedExtension' => '0',
      ],
      'Test case 2: with #' => [
        'extension' => '#3',
        'expectedExtension' => '3',
      ],
      'Test case 3: with dashes' => [
        'extension' => '1-1826',
        'expectedExtension' => '11826',
      ],
      'Test case 4: numbers with commas' => [
        'extension' => '1442, 6685',
        'expectedExtension' => '1442, 6685',
      ],
      'Test case 5: numbers with or' => [
        'extension' => '2097 or 2098',
        'expectedExtension' => '2097 or 2098',
      ],
      'Test case 6: numbers with /' => [
        'extension' => '2220/2221',
        'expectedExtension' => '2220/2221',
      ],
      'Test case 7: numbers with then' => [
        'extension' => '4 then 2',
        'expectedExtension' => '4 then 2',
      ],
      'Test case 8: numbers with a word' => [
        'extension' => 'Dial 1',
        'expectedExtension' => '1',
      ],
    ];
  }

}
