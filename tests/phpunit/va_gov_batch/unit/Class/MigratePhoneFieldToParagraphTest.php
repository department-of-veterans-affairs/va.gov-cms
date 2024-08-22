<?php

namespace tests\phpunit\va_gov_batch\unit\Class;

use Drupal\va_gov_batch\cbo_scripts\MigratePhoneFieldToParagraph;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit test for MigratePhoneFieldToParagraph::extractPhoneAndExtension.
 *
 * @group va_gov_batch
 */
class MigratePhoneFieldToParagraphTest extends TestCase {

  /**
   * Test the extractPhoneAndExtension method.
   *
   * @dataProvider phoneNumberProvider
   */
  public function testExtractPhoneAndExtension($input, $expectedPhone, $expectedExtension) {
    $result = MigratePhoneFieldToParagraph::extractPhoneAndExtension($input);
    $this->assertSame($expectedPhone, $result['phone']);
    $this->assertSame($expectedExtension, $result['extension']);
  }

  /**
   * Data provider for testExtractPhoneAndExtension.
   */
  public function phoneNumberProvider() {
    return [
      'Test case 1: with ext keyword' => [
        'input' => '718-584-9000, ext. 4400',
        'expectedPhone' => '718-584-9000',
        'expectedExtension' => '4400',
      ],
      'Test case 2: with x keyword' => [
        'input' => '205-933-8101 x4737',
        'expectedPhone' => '205-933-8101',
        'expectedExtension' => '4737',
      ],
      'Test case 3: without extension' => [
        'input' => '+1-918-781-5678',
        'expectedPhone' => '918-781-5678',
        'expectedExtension' => '',
      ],
      'Test case 4: with text and phone' => [
        'input' => '888-GIBILL-1 (888-442-4551)',
        'expectedPhone' => '888-442-4551',
        'expectedExtension' => '',
      ],
      'Test case 5: multiple phone numbers with extension' => [
        'input' => '602-277-5551, ext. 2173 or 800-554-7174 (toll free)',
        'expectedPhone' => '602-277-5551',
        'expectedExtension' => '2173',
      ],
      'Test case 6: multiple extensions' => [
        'input' => '304-263-0811, ext. 3300 or 3302',
        'expectedPhone' => '304-263-0811',
        'expectedExtension' => '3300',
      ],
    ];
  }

}
