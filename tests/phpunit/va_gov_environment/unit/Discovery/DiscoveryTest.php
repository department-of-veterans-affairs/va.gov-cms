<?php

namespace tests\phpunit\va_gov_environment\unit\Discovery;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_environment\Discovery\Discovery;
use Drupal\va_gov_environment\Environment\Environment;
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
   * @covers ::__construct
   * @covers ::getRawEnvironment
   * @covers ::getRawValue
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
    $this->assertEquals('ddev', $discoveryService->getRawValue());
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

  /**
   * Confirm that environments are detected as intended.
   *
   * @param string $environmentName
   *   The name of the environment.
   * @param bool $isProduction
   *   Whether the environment is expected to be production.
   * @param bool $isStaging
   *   Whether the environment is expected to be staging.
   * @param bool $isDev
   *   Whether the environment is expected to be dev.
   * @param bool $isTugboat
   *   Whether the environment is expected to be Tugboat.
   * @param bool $isDdev
   *   Whether the environment is expected to be DDEV.
   *
   * @covers ::isProduction
   * @covers ::isStaging
   * @covers ::isDev
   * @covers ::isTugboat
   * @covers ::isLocalDev
   * @covers ::isBrd
   * @dataProvider isWhateverDataProvider
   */
  public function testIsWhatever(string $environmentName, bool $isProduction, bool $isStaging, bool $isDev, bool $isTugboat, bool $isDdev) {
    $settings = new Settings([
      'va_gov_environment' => [
        'environment' => $environmentName,
      ],
    ]);
    $discovery = new Discovery($settings);

    $this->assertEquals($isProduction, $discovery->isProduction());
    $this->assertEquals($isStaging, $discovery->isStaging());
    $this->assertEquals($isDev, $discovery->isDev());
    $this->assertEquals($isTugboat, $discovery->isTugboat());
    $this->assertEquals($isDdev, $discovery->isLocalDev());
    $this->assertEquals($isProduction || $isStaging || $isDev, $discovery->isBrd());
  }

  /**
   * Data provider for testIsWhatever.
   *
   * @return array
   *   The data.
   */
  public function isWhateverDataProvider() {
    return [
      'ddev' => [
        'environmentName' => 'ddev',
        'isProduction' => FALSE,
        'isStaging' => FALSE,
        'isDev' => FALSE,
        'isTugboat' => FALSE,
        'isDdev' => TRUE,
      ],
      'tugboat' => [
        'environmentName' => 'tugboat',
        'isProduction' => FALSE,
        'isStaging' => FALSE,
        'isDev' => FALSE,
        'isTugboat' => TRUE,
        'isDdev' => FALSE,
      ],
      'dev' => [
        'environmentName' => 'dev',
        'isProduction' => FALSE,
        'isStaging' => FALSE,
        'isDev' => TRUE,
        'isTugboat' => FALSE,
        'isDdev' => FALSE,
      ],
      'staging' => [
        'environmentName' => 'staging',
        'isProduction' => FALSE,
        'isStaging' => TRUE,
        'isDev' => FALSE,
        'isTugboat' => FALSE,
        'isDdev' => FALSE,
      ],
      'prod' => [
        'environmentName' => 'prod',
        'isProduction' => TRUE,
        'isStaging' => FALSE,
        'isDev' => FALSE,
        'isTugboat' => FALSE,
        'isDdev' => FALSE,
      ],
    ];
  }

}
