<?php

namespace Tests\va_gov_github\unit\Api\Settings;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_github\Api\Settings\ApiSettings;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the API Settings class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_github\Api\Settings\ApiSettings
 */
class ApiSettingsTest extends VaGovUnitTestBase {

  /**
   * Test construction.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $settings = new Settings([]);
    $apiSettings = new ApiSettings($settings);
    $this->assertInstanceOf(ApiSettings::class, $apiSettings);
  }

  /**
   * Test getApiToken().
   *
   * @covers ::getApiToken
   */
  public function testGetApiToken() {
    $settings = new Settings([
      'va_cms_bot_github_auth_token' => 'fake_token',
    ]);
    $apiSettings = new ApiSettings($settings);
    $this->assertEquals('fake_token', $apiSettings->getApiToken());
  }

}
