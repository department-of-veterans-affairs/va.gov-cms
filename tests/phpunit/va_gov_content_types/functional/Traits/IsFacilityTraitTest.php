<?php

namespace tests\phpunit\va_gov_content_types\functional\Traits;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the IsFacilityTrait trait.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Traits\IsFacilityTrait
 */
class IsFacilityTraitTest extends VaGovExistingSiteBase {

  /**
   * Test content types and verify that the isFacility method works.
   *
   * @param string $type
   *   The content type to test.
   * @param bool $expected
   *   The expected result of the isFacility method.
   *
   * @covers ::isFacility
   * @dataProvider isFacilityDataProvider
   */
  public function testIsFacility(string $type, bool $expected) {
    $node = $this->getArbitraryNodeOfType($type);
    $this->assertEquals($expected, $node->isFacility());
  }

  /**
   * Data provider for testIsFacility.
   *
   * @return array
   *   An array of arrays, each containing a content type and the expected
   *   result of the isFacility method.
   */
  public function isFacilityDataProvider() {
    return [
      'health_care_local_health_service' => [
        'health_care_local_health_service',
        FALSE,
      ],
      'health_care_local_facility' => ['health_care_local_facility', TRUE],
      'nca_facility' => ['nca_facility', FALSE],
      'vba_facility' => ['vba_facility', TRUE],
      'vet_center_cap' => ['vet_center_cap', TRUE],
      'vet_center_outstation' => ['vet_center_outstation', TRUE],
      'vet_center' => ['vet_center', TRUE],
      'page' => ['page', FALSE],
      'landing_page' => ['landing_page', FALSE],
    ];
  }

}
