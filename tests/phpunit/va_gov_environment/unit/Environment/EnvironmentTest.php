<?php

namespace tests\phpunit\va_gov_environment\unit\Environment;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_environment\Environment\Environment;
use Drupal\va_gov_environment\Service\Discovery;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Environment enum.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_environment\Environment\Environment
 */
class EnvironmentTest extends VaGovUnitTestBase {

  /**
   * Test that the environment can be constructed for all cases.
   *
   * @param string $environmentName
   *   The name of the environment.
   *
   * @covers ::fromSettings
   * @dataProvider fromSettingsDataProvider
   */
  public function testFromSettings(string $environmentName) {
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
   * Data provider for testGetEnvironment.
   *
   * @return array
   *   The data.
   */
  public function fromSettingsDataProvider() {
    return [
      [
        'ddev',
      ],
      [
        'tugboat',
      ],
      [
        'staging',
      ],
      [
        'prod',
      ],
    ];
  }

  /**
   * Test that a ValueError is thrown when an invalid environment is provided.
   */
  public function testInvalidEnvironment() {
    $this->expectException(\ValueError::class);
    Environment::from('invalid');
  }

  /**
   * Confirm that production environments are detected as intended.
   *
   * @param string $environmentName
   *   The name of the environment.
   * @param bool $expected
   *   Whether the environment is expected to be production.
   *
   * @covers ::isProduction
   * @dataProvider isProductionDataProvider
   */
  public function testIsProduction(string $environmentName, bool $expected) {
    $environment = Environment::from($environmentName);
    $this->assertEquals($expected, $environment->isProduction());
  }

  /**
   * Data provider for testIsProduction.
   *
   * @return array
   *   The data.
   */
  public function isProductionDataProvider() {
    return [
      [
        'ddev',
        FALSE,
      ],
      [
        'tugboat',
        FALSE,
      ],
      [
        'staging',
        FALSE,
      ],
      [
        'prod',
        TRUE,
      ],
    ];
  }

  /**
   * Confirm that staging environments are detected as intended.
   *
   * @param string $environmentName
   *   The name of the environment.
   * @param bool $expected
   *   Whether the environment is expected to be staging.
   *
   * @covers ::isStaging
   * @dataProvider isStagingDataProvider
   */
  public function testIsStaging(string $environmentName, bool $expected) {
    $environment = Environment::from($environmentName);
    $this->assertEquals($expected, $environment->isStaging());
  }

  /**
   * Data provider for testIsStaging.
   *
   * @return array
   *   The data.
   */
  public function isStagingDataProvider() {
    return [
      [
        'ddev',
        FALSE,
      ],
      [
        'tugboat',
        FALSE,
      ],
      [
        'staging',
        TRUE,
      ],
      [
        'prod',
        FALSE,
      ],
    ];
  }

}
