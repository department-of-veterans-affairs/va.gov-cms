<?php

namespace tests\phpunit\Ops;

use Drupal\Core\Site\Settings;
use Drupal\prometheus_exporter\MetricsCollectorManager;
use Drupal\va_gov_backend\Service\Datadog;
use Drupal\va_gov_backend\Service\Metrics;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test the Metrics service.
 */
class MetricsTest extends ExistingSiteBase {

  /**
   * Tests the shouldSendMetrics method.
   *
   * @dataProvider shouldSendMetricsDataProvider
   */
  public function testShouldSendMetrics($settings_values, $expected_result) {
    $this->protectedMethodTest('shouldSendMetrics', $settings_values, $expected_result);
  }

  /**
   * Tests the getEnvironment method.
   *
   * @dataProvider getEnvironmentDataProvider
   */
  public function testGetEnvironment($settings_values, $expected_result) {
    $this->protectedMethodTest('getEnvironment', $settings_values, $expected_result);
  }

  /**
   * Executes a protected method in the Metrics service and tests the result.
   */
  protected function protectedMethodTest($method, $settings_values, $expected_result) {
    $metrics_collector_manager = $this->createMock(MetricsCollectorManager::class);
    $settings = new Settings($settings_values);
    $datadog = $this->createMock(Datadog::class);

    $metrics = new Metrics($metrics_collector_manager, $settings, $datadog);

    $ssm = new \ReflectionMethod(Metrics::class, $method);
    $ssm->setAccessible(TRUE);
    $result = $ssm->invoke($metrics);

    $this->assertEquals($expected_result, $result);
  }

  /**
   * Provides data to testGetEnvironment.
   */
  public function getEnvironmentDataProvider() {
    return [
      'Nothing configured' => [
        [],
        "unknown",
      ],
      'BRDGHA build type, but no deploy env' => [
        [
          "va_gov_frontend_build_type" => "brdgha",
        ],
        "unknown",
      ],
      'Some random build type' => [
        [
          "va_gov_frontend_build_type" => "foobar",
        ],
        "unknown",
      ],
      'BRDGHA build type with deploy env' => [
        [
          "va_gov_frontend_build_type" => "brdgha",
          "github_actions_deploy_env" => "prod",
        ],
        "prod",
      ],
      'BRDGHA build type with a different deploy env' => [
        [
          "va_gov_frontend_build_type" => "brdgha",
          "github_actions_deploy_env" => "whateverenv",
        ],
        "whateverenv",
      ],
      'Lando build type' => [
        [
          "va_gov_frontend_build_type" => "lando",
        ],
        "local",
      ],
    ];
  }

  /**
   * Provides data to testShouldSendMetrics.
   */
  public function shouldSendMetricsDataProvider() {
    return [
      'Nothing configured' => [
        [],
        FALSE,
      ],
      'BRDGHA environment' => [
        [
          'va_gov_frontend_build_type' => 'brdgha',
        ],
        TRUE,
      ],
      'Lando environment' => [
        [
          'va_gov_frontend_build_type' => 'lando',
        ],
        FALSE,
      ],
      'Force sending metrics enabled' => [
        [
          'va_gov_force_sending_metrics' => TRUE,
        ],
        TRUE,
      ],
      'BRDGHA environment and force sending metrics explicitly set to false' => [
        [
          'va_gov_frontend_build_type' => 'brdgha',
          'va_gov_force_sending_metrics' => FALSE,
        ],
        TRUE,
      ],
      'Lando environment + force sending metrics enabled' => [
        [
          'va_gov_frontend_build_type' => 'lando',
          'va_gov_force_sending_metrics' => TRUE,
        ],
        TRUE,
      ],
      'Lando environment + force sending metrics explicitly set to false' => [
        [
          'va_gov_frontend_build_type' => 'lando',
          'va_gov_force_sending_metrics' => FALSE,
        ],
        FALSE,
      ],
    ];
  }

}
