<?php

namespace tests\phpunit\va_gov_environment\unit\Strategy\Resolver;

use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManagerInterface;
use Drupal\va_gov_content_release\Strategy\Resolver\Resolver;
use Drupal\va_gov_environment\Environment\Environment;
use Drupal\va_gov_environment\Service\DiscoveryInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Strategy Resolver service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Strategy\Resolver\Resolver
 */
class ResolverTest extends VaGovUnitTestBase {

  /**
   * Test that the reporter object will be constructed.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $environmentDiscoveryProphecy = $this->prophesize(DiscoveryInterface::class);
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $strategyPluginManagerProphecy = $this->prophesize(StrategyPluginManagerInterface::class);
    $strategyPluginManager = $strategyPluginManagerProphecy->reveal();
    $resolver = new Resolver($strategyPluginManager, $environmentDiscovery);
    $this->assertInstanceOf(Resolver::class, $resolver);
  }

  /**
   * Test the strategy mapping.
   *
   * @param string $environmentId
   *   The environment ID.
   * @param string $strategyId
   *   The expected strategy ID.
   * @param \Throwable $exception
   *   The expected exception.
   *
   * @covers ::getStrategyId
   * @dataProvider getStrategyIdProvider
   */
  public function testGetStrategyId(string $environmentId, string $strategyId = NULL, \Throwable $exception = NULL) {
    if ($exception) {
      $this->expectException(get_class($exception));
    }
    $environmentDiscoveryProphecy = $this->prophesize(DiscoveryInterface::class);
    $environmentDiscoveryProphecy->getEnvironment()->willReturn(Environment::from($environmentId));
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $strategyPluginManagerProphecy = $this->prophesize(StrategyPluginManagerInterface::class);
    $strategyPluginManager = $strategyPluginManagerProphecy->reveal();
    $resolver = new Resolver($strategyPluginManager, $environmentDiscovery);
    $this->assertEquals($strategyId, $resolver->getStrategyId());
  }

  /**
   * Data provider for testGetStrategyId().
   *
   * @return array
   *   The test data.
   */
  public function getStrategyIdProvider() {
    return [
      'ddev' => [
        'ddev',
        'local_filesystem_build_file',
        NULL,
      ],
      'prod' => [
        'prod',
        'github_repository_dispatch',
        NULL,
      ],
      'staging' => [
        'staging',
        'github_repository_dispatch',
        NULL,
      ],
      'tugboat' => [
        'tugboat',
        'local_filesystem_build_file',
        NULL,
      ],
      'invalid' => [
        'invalid',
        NULL,
        new \ValueError('Invalid environment ID: invalid'),
      ],
    ];
  }

  /**
   * Test triggering a content release.
   *
   * @covers ::triggerContentRelease
   */
  public function testTriggerContentRelease() {
    $environmentDiscoveryProphecy = $this->prophesize(DiscoveryInterface::class);
    $environmentDiscoveryProphecy->getEnvironment()->willReturn(Environment::from('ddev'));
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $strategyPluginManagerProphecy = $this->prophesize(StrategyPluginManagerInterface::class);
    $strategyPluginManager = $strategyPluginManagerProphecy->reveal();
    $resolver = new Resolver($strategyPluginManager, $environmentDiscovery);
    $resolver->triggerContentRelease();
    $strategyPluginManagerProphecy->triggerContentRelease('local_filesystem_build_file')->shouldHaveBeenCalled();
  }

}
