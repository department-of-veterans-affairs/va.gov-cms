<?php

namespace tests\phpunit\va_gov_content_types\functional\Traits;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the ContentReleaseTriggerTrait trait.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Traits\ContentReleaseTriggerTrait
 */
class ContentReleaseTriggerTraitTest extends VaGovExistingSiteBase {

  /**
   * Verify that content types that trigger a release are detected correctly.
   *
   * @param string $type
   *   The content type.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::alwaysTriggersContentRelease
   * @dataProvider alwaysTriggersContentReleaseDataProvider
   */
  public function testAlwaysTriggersContentRelease(string $type, bool $expected) {
    $node = $this->getArbitraryNodeOfType($type);
    $this->assertEquals($expected, $node->alwaysTriggersContentRelease());
  }

  /**
   * Data provider for testAlwaysTriggersContentRelease.
   *
   * @return array
   *   An array of arrays, each containing a content type and the expected
   *   result of the alwaysTriggersContentRelease method.
   */
  public function alwaysTriggersContentReleaseDataProvider() {
    return [
      'banner' => [
        'type' => 'banner',
        'expected' => TRUE,
      ],
      'page' => [
        'type' => 'page',
        'expected' => FALSE,
      ],
      'health_care_local_facility' => [
        'type' => 'health_care_local_facility',
        'expected' => FALSE,
      ],
      'full_width_banner_alert' => [
        'type' => 'full_width_banner_alert',
        'expected' => TRUE,
      ],
    ];
  }

}
