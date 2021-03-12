<?php

namespace test\phpunit\Deploy;

use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Deploy\DeployService;
use Drupal\va_gov_backend\Deploy\Plugin\DeployPluginInterface;
use Drupal\va_gov_backend\Deploy\SuccessHTTPException;
use Drupal\va_gov_backend\Test\DeployServiceMock;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\va_gov_backend\Deploy\DeployService
 */
class DeployServiceTest extends UnitTestCase {

  /**
   * Test the deploy service run class.
   *
   * @covers \Drupal\va_gov_backend\Deploy\DeployService::run
   */
  public function testRun() {
    $mock_deploy_service = DeployServiceMock::create();

    $exception = new SuccessHTTPException('new error');
    $this->expectExceptionObject($exception);

    $request = Request::createFromGlobals();
    $mock_deploy_service->run($request, 'path', 'path');
  }

  /**
   * Test creation of the DeployService.
   *
   * @covers \Drupal\va_gov_backend\Deploy\DeployService::create
   */
  public function testCreate() {
    $orig_settings = [
      'item_1' => 'value 1',
      'item_2' => 'value 2',
    ];

    DeployService::create($orig_settings);

    $settings = Settings::getAll();
    $this->assertArrayEquals(
      $orig_settings,
      $settings,
      'Deploy Serviced initiated Settings correctly.'
    );
  }

  /**
   * Test to make sure DeployPluginInterfaces are returned.
   *
   * @covers \Drupal\va_gov_backend\Deploy\DeployService::deployPlugins
   */
  public function testDeployPlugins() {
    $plugins = DeployService::deployPlugins();

    foreach ($plugins as $plugin) {
      $plugin_obj = new $plugin();
      static::assertInstanceOf(
        DeployPluginInterface::class,
        $plugin_obj
      );
    }
  }

}
