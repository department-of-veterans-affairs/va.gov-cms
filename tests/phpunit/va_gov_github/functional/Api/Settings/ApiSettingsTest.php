<?php

namespace tests\phpunit\va_gov_github\functional\Api\Settings;

use Drupal\va_gov_github\Api\Settings\ApiSettings;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_github\Api\Settings\ApiSettings
 */
class ApiSettingsTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(ApiSettings::class, \Drupal::service('va_gov_github.api_settings'));
  }

}
