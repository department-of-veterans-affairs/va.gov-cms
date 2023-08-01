<?php

namespace tests\phpunit\va_gov_content_types\functional\Entity;

use Drupal\va_gov_content_types\Entity\HealthCareLocalFacility;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the HealthCareLocalFacility class.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Entity\HealthCareLocalFacility
 */
class HealthCareLocalFacilityTest extends VaGovExistingSiteBase {

  /**
   * Verify that `health_care_local_facility` nodes have this bundle class.
   */
  public function testBundleClass() {
    $node = $this->createNode([
      'type' => 'health_care_local_facility',
    ]);
    $this->assertEquals(HealthCareLocalFacility::class, get_class($node));
  }

}
