<?php

namespace tests\phpunit\va_gov_batch\unit\Class;

use Drupal\va_gov_batch\cbo_scripts\SplitExtensionWithTwoNumbers;
use PHPUnit\Framework\TestCase;

/**
 * Test for SplitExtensionWithTwoNumbers::splitExtensions.
 *
 * @group va_gov_batch
 */
class SplitExtensionWithTwoNumbersTest extends TestCase {

  /**
   * Test the splitExtensions method.
   *
   * @dataProvider extensionsProvider
   */
  public function testSplitExtensionWithTwoNumbers($extension, $expectedExtension) {
    $result = SplitExtensionWithTwoNumbers::splitExtensions($extension);

    $this->assertSame($expectedExtension, $result);
  }

  /**
   * Data provider for testSplitExtensionWithTwoNumbers.
   */
  public function extensionsProvider() {
    return [
      'Test case 1: numbers with commas' => [
        'extension' => '1442, 6685',
        'expectedExtension' => ['1442', '6685'],
      ],
      'Test case 2: numbers with or' => [
        'extension' => '2097 or 2098',
        'expectedExtension' => ['2097', '2098'],
      ],
      'Test case 6: numbers with /' => [
        'extension' => '2220/2221',
        'expectedExtension' => ['2220', '2221'],
      ],
    ];
  }

}
