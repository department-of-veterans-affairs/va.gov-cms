<?php

namespace tests\phpunit\Environment;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_environment\Service\Discovery;
use Drupal\va_gov_environment\Service\DiscoveryInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Environment Discovery service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_environment\Service\Discovery
 */
class DiscoveryTest extends VaGovUnitTestBase {

  /**
   * Test that the raw environment matches one of our expected values.
   *
   * DDEV uses the string "local" rather than "ddev". This will change in the
   * processed environment.
   *
   * @covers ::getRawEnvironment
   */
  public function testGetRawEnvironment() {
    $settings = new Settings([
      'va_gov_environment' => [
        'environment_raw' => 'local',
      ],
    ]);
    $discoveryService = new Discovery($settings);
    $this->assertEquals('local', $discoveryService->getRawEnvironment());
  }

  /**
   * Test that the processed environment matches one of our expected values.
   *
   * @covers ::getEnvironment
   * @dataProvider getEnvironmentDataProvider
   */
  public function testGetEnvironment($va_gov_environment, $expected) {
    $settings = new Settings([
      'va_gov_environment' => $va_gov_environment,
    ]);
    $discoveryService = new Discovery($settings);
    $this->assertEquals($expected, $discoveryService->getEnvironment());
  }

  /**
   * Data provider for testGetEnvironment.
   *
   * @return array
   *   The data.
   */
  public function getEnvironmentDataProvider() {
    return [
      [
        [
          'environment' => 'ddev',
        ],
        DiscoveryInterface::ENVIRONMENT_DDEV,
      ],
      [
        [
          'environment' => 'tugboat',
        ],
        DiscoveryInterface::ENVIRONMENT_TUGBOAT,
      ],
      [
        [
          'environment' => 'staging',
        ],
        DiscoveryInterface::ENVIRONMENT_STAGING,
      ],
      [
        [
          'environment' => 'prod',
        ],
        DiscoveryInterface::ENVIRONMENT_PROD,
      ],
    ];
  }

  /**
   * Test that CMS-TEST is detected correctly.
   *
   * @covers ::isCmsTest
   * @dataProvider isCmsTestDataProvider
   */
  public function testIsCmsTest($va_gov_environment, bool $expected) {
    $settings = new Settings([
      'va_gov_environment' => $va_gov_environment,
    ]);
    $discoveryService = new Discovery($settings);
    $this->assertEquals($expected, $discoveryService->isCmsTest());
  }

  /**
   * Data provider for testIsCmsTest.
   *
   * @return array
   *   The data.
   */
  public function isCmsTestDataProvider() {
    return [
      [
        [
          'is_cms_test' => FALSE,
        ],
        FALSE,
      ],
      [
        [
          'is_cms_test' => TRUE,
        ],
        TRUE,
      ],
    ];
  }

}
