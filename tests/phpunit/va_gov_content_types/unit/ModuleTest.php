<?php

namespace tests\phpunit\va_gov_content_types\unit;

use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_content_types\Entity\HealthCareLocalFacility;
use Drupal\va_gov_content_types\Entity\VaNode;
use Tests\Support\Classes\VaGovUnitTestBase;

require __DIR__ . '/../../../../docroot/modules/custom/va_gov_content_types/va_gov_content_types.module';

/**
 * Unit test of the va_gov_content_types module hooks.
 *
 * @group unit
 * @group all
 */
class ModuleTest extends VaGovUnitTestBase {

  use GeneratePermutationsTrait;

  /**
   * Test the hasTriggeringChanges() method.
   *
   * @param bool $contentType
   *   The principle content type.
   * @param bool $bundleClass
   *   The expected bundle class.
   *
   * @covers va_gov_content_types_entity_bundle_info_alter()
   * @dataProvider entityBundleInfoAlterDataProvider
   */
  public function testEntityBundleInfoAlter(string $contentType, string $bundleClass) {
    $bundles = [
      'node' => [
        $contentType => [
          'class' => $bundleClass,
        ],
      ],
    ];
    \va_gov_content_types_entity_bundle_info_alter($bundles);
    $this->assertEquals($bundles['node'][$contentType]['class'], $bundleClass);
  }

  /**
   * Data provider for testEntityBundleInfoAlter.
   *
   * @return array
   *   An array of arrays.
   */
  public function entityBundleInfoAlterDataProvider() {
    return [
      'page' => [
        'page',
        VaNode::class,
      ],
      'health_care_local_facility' => [
        'health_care_local_facility',
        HealthCareLocalFacility::class,
      ],
    ];
  }

}
