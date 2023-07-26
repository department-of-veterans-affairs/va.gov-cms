<?php

namespace tests\phpunit\va_gov_environment\unit\EntityEvent\Strategy\Resolver;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\Resolver;
use Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\ResolverInterface;
use Drupal\va_gov_environment\Environment\Environment;
use Drupal\va_gov_environment\Discovery\DiscoveryInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Entity Event Strategy Resolver service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\Resolver
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
    $resolver = new Resolver($environmentDiscovery);
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
    $resolver = new Resolver($environmentDiscovery);
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
        ResolverInterface::STRATEGY_NEVER,
        NULL,
      ],
      'prod' => [
        'prod',
        ResolverInterface::STRATEGY_ON_DEMAND,
        NULL,
      ],
      'staging' => [
        'staging',
        ResolverInterface::STRATEGY_ON_DEMAND,
        NULL,
      ],
      'tugboat' => [
        'tugboat',
        ResolverInterface::STRATEGY_NEVER,
        NULL,
      ],
      'invalid' => [
        'invalid',
        NULL,
        new \ValueError('Invalid environment ID: invalid'),
      ],
    ];
  }

}
