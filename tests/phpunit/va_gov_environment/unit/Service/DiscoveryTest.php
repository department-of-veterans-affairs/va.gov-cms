<?php

namespace tests\phpunit\va_gov_environment\unit\Service;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_environment\Environment\Environment;
use Drupal\va_gov_environment\Discovery\Discovery;
use Drupal\va_gov_environment\Exception\InvalidEnvironmentException;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Environment Discovery service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_environment\Discovery\Discovery
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
        'environment' => 'ddev',
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
  public function testGetEnvironment($va_gov_environment, Environment $expected) {
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
        Environment::Ddev,
      ],
      [
        [
          'environment' => 'tugboat',
        ],
        Environment::Tugboat,
      ],
      [
        [
          'environment' => 'staging',
        ],
        Environment::Staging,
      ],
      [
        [
          'environment' => 'prod',
        ],
        Environment::Prod,
      ],
    ];
  }

  /**
   * Test that the processed environment matches one of our expected values.
   *
   * @covers ::getEnvironment
   */
  public function testGetEnvironment2() {
    $settings = new Settings([
      'va_gov_environment' => [
        'environment' => 'unknown',
      ],
    ]);
    $this->expectException(InvalidEnvironmentException::class);
    new Discovery($settings);
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
          'environment' => 'staging',
        ],
        FALSE,
      ],
      [
        [
          'is_cms_test' => TRUE,
          'environment' => 'staging',
        ],
        TRUE,
      ],
    ];
  }

}
