<?php

namespace tests\phpunit\va_gov_environment\unit\Environment;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_environment\Environment\Environment;
use Drupal\va_gov_environment\Discovery\Discovery;
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
   * @covers ::isDdev
   * @covers ::isLocalDev
   * @covers ::isBrd
   * @dataProvider isWhateverDataProvider
   */
  public function testIsWhatever(string $environmentName, bool $isProduction, bool $isStaging, bool $isDev, bool $isTugboat, bool $isDdev) {
    $environment = Environment::from($environmentName);
    $this->assertEquals($isProduction, $environment->isProduction());
    $this->assertEquals($isStaging, $environment->isStaging());
    $this->assertEquals($isDev, $environment->isDev());
    $this->assertEquals($isTugboat, $environment->isTugboat());
    $this->assertEquals($isDdev, $environment->isDdev());
    $this->assertEquals($isDdev, $environment->isLocalDev());
    $this->assertEquals($isProduction || $isStaging || $isDev, $environment->isBrd());
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
