<?php

namespace tests\phpunit\va_gov_content_release;

use Drupal\va_gov_content_release\Exception\StrategyErrorException;
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

}
