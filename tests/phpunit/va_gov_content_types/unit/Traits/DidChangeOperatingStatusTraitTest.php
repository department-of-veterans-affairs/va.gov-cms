<?php

namespace tests\phpunit\va_gov_content_types\unit\Traits;

use Drupal\va_gov_content_types\Exception\NonFacilityException;
use Drupal\va_gov_content_types\Traits\DidChangeOperatingStatusTrait;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the DidChangeOperatingStatusTrait trait.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_types\Traits\DidChangeOperatingStatusTrait
 */
class DidChangeOperatingStatusTraitTest extends VaGovUnitTestBase {

  /**
   * Test the didChangeOperatingStatus() function.
   *
   * @param bool $isFacility
   *   Whether this was a facility.
   * @param bool $hasStatusField
   *   Whether the node has a status field.
   * @param bool $didChangeField
   *   Whether the status field changed.
   * @param bool $hasOriginal
   *   Whether the node has an original.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::didChangeOperatingStatus
   * @dataProvider didChangeOperatingStatusDataProvider
   */
  public function testDidChangeOperatingStatus(
    bool $isFacility,
    bool $hasStatusField,
    bool $didChangeField,
    bool $hasOriginal,
    bool $expected
  ) : void {
    $node = $this->getMockForTrait(DidChangeOperatingStatusTrait::class);
    $node->expects($this->any())->method('isFacility')->will($this->returnValue($isFacility));
    $node->expects($this->any())->method('hasField')->will($this->returnValue($hasStatusField));
    $node->expects($this->any())->method('didChangeField')->will($this->returnValue($didChangeField));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue($hasOriginal));
    if (!$isFacility) {
      $this->expectException(NonFacilityException::class);
    }
    $this->assertEquals($expected, $node->didChangeOperatingStatus());
  }

  /**
   * Data provider for testDidChangeOperatingStatus.
   *
   * @return array
   *   An array of arrays.
   */
  public function didChangeOperatingStatusDataProvider() {
    return [
      'not a facility' => [
        FALSE,
        FALSE,
        FALSE,
        FALSE,
        FALSE,
      ],
      'no field' => [
        TRUE,
        FALSE,
        FALSE,
        FALSE,
        FALSE,
      ],
      'no value' => [
        TRUE,
        TRUE,
        FALSE,
        FALSE,
        FALSE,
      ],
      'changed' => [
        TRUE,
        TRUE,
        TRUE,
        TRUE,
        TRUE,
      ],
      'not changed' => [
        TRUE,
        TRUE,
        FALSE,
        TRUE,
        FALSE,
      ],
    ];
  }

}
