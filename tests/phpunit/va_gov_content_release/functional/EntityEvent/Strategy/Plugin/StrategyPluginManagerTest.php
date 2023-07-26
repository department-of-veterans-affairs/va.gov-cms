<?php

namespace tests\phpunit\va_gov_content_release\functional\EntityEvent\Strategy\Plugin;

use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginInterface;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Strategy Plugin Manager service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginManager
 */
class StrategyPluginManagerTest extends VaGovExistingSiteBase {

  /**
   * Test that the Strategy Plugin Manager service is available.
   *
   * @covers ::__construct
   * @covers ::getDefinitions
   */
  public function testGetDefinitions() {
    $definitions = $this->container->get('plugin.manager.va_gov_content_release.entity_event_strategy')->getDefinitions();
    $this->assertNotEmpty($definitions);
  }

  /**
   * Test that the Strategy Plugin Manager service can execute a strategy.
   *
   * @covers ::shouldTriggerContentRelease
   * @covers ::getStrategy
   */
  public function testShouldTriggerContentReleaseTrue() {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $node = $nodeProphecy->reveal();
    $result = $this->container->get('plugin.manager.va_gov_content_release.entity_event_strategy')->shouldTriggerContentRelease('test_true', $node);
    $this->assertEquals(TRUE, $result);
  }

  /**
   * Test that the Strategy Plugin Manager service can execute a strategy.
   *
   * @covers ::shouldTriggerContentRelease
   * @covers ::getStrategy
   */
  public function testShouldTriggerContentReleaseFalse() {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $node = $nodeProphecy->reveal();
    $result = $this->container->get('plugin.manager.va_gov_content_release.entity_event_strategy')->shouldTriggerContentRelease('test_false', $node);
    $this->assertEquals(FALSE, $result);
  }

  /**
   * Test that the Strategy Plugin Manager propagates exceptions.
   *
   * @covers ::triggerContentRelease
   * @covers ::getStrategy
   */
  public function testTriggerContentReleaseException() {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $node = $nodeProphecy->reveal();
    $this->expectException(StrategyErrorException::class);
    $this->container->get('plugin.manager.va_gov_content_release.entity_event_strategy')->shouldTriggerContentRelease('test_exception', $node);
  }

  /**
   * Test that the Strategy Plugin Manager's on-demand strategy works.
   *
   * @param bool $shouldTrigger
   *   Whether the node should trigger a content release.
   *
   * @covers ::triggerContentRelease
   * @covers ::getStrategy
   * @dataProvider triggerContentReleaseOnDemandDataProvider
   */
  public function testTriggerContentReleaseOnDemand($shouldTrigger) {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $nodeProphecy->shouldTriggerContentRelease()->willReturn($shouldTrigger)->shouldBeCalled();
    $node = $nodeProphecy->reveal();
    $actual = $this->container->get('plugin.manager.va_gov_content_release.entity_event_strategy')->shouldTriggerContentRelease('on_demand', $node);
    $this->assertEquals($shouldTrigger, $actual);
  }

  /**
   * Data provider for testTriggerContentReleaseOnDemand().
   *
   * @return array
   *   An array of booleans.
   */
  public function triggerContentReleaseOnDemandDataProvider() {
    return [
      [TRUE],
      [FALSE],
    ];
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
    $plugin = \Drupal::service('plugin.manager.va_gov_content_release.entity_event_strategy')->getStrategy($pluginId);
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
      ['test_true'],
      ['test_false'],
      ['test_exception'],
      ['on_demand'],
    ];
  }

}
