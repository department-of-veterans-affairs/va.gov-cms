<?php

namespace tests\phpunit\va_gov_content_release\functional\Plugin\Strategy;

use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginInterface;
use Drupal\va_gov_content_release\Strategy\Resolver\ResolverInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Strategy Plugin Manager service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManager
 */
class StrategyPluginManagerTest extends VaGovExistingSiteBase {

  /**
   * Test that the Strategy Plugin Manager service is available.
   *
   * @covers ::getDefinitions
   */
  public function testGetDefinitions() {
    $definitions = $this->container->get('plugin.manager.va_gov_content_release.strategy')->getDefinitions();
    $this->assertNotEmpty($definitions);
  }

  /**
   * Test that the Strategy Plugin Manager service can execute a strategy.
   *
   * @covers ::triggerContentRelease
   * @covers ::getStrategy
   */
  public function testTriggerContentRelease() {
    $this->container->get('plugin.manager.va_gov_content_release.strategy')->triggerContentRelease('test_success');
    $this->assertTrue(TRUE);
  }

  /**
   * Test that the Strategy Plugin Manager propagates exceptions.
   *
   * @covers ::triggerContentRelease
   * @covers ::getStrategy
   */
  public function testTriggerContentReleaseException() {
    $this->expectException(StrategyErrorException::class);
    $this->container->get('plugin.manager.va_gov_content_release.strategy')->triggerContentRelease('test_exception');
  }

  /**
   * Test that we can instantiate the necessary plugins.
   *
   * @param string $pluginId
   *   A strategy plugin ID to retrieve.
   *
   * @dataProvider getStrategyDataProvider
   */
  public function testGetStrategy(string $pluginId) {
    $plugin = \Drupal::service('plugin.manager.va_gov_content_release.strategy')->createInstance($pluginId);
    $this->assertInstanceOf(StrategyPluginInterface::class, $plugin);
  }

  /**
   * Data provider for testGetStrategy().
   *
   * @return array
   *   An array of plugin IDs.
   */
  public function getStrategyDataProvider() {
    return [
      ['test_success'],
      ['test_exception'],
      [ResolverInterface::STRATEGY_GITHUB_REPOSITORY_DISPATCH],
      [ResolverInterface::STRATEGY_LOCAL_FILESYSTEM_BUILD_FILE],
    ];
  }

}
