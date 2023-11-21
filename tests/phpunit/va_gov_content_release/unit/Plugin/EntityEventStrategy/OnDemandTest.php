<?php

namespace tests\phpunit\va_gov_content_release\unit\Plugin\EntityEventStrategy;

use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\va_gov_content_release\Plugin\EntityEventStrategy\OnDemand;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the On-Demand entity event strategy plugin.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Plugin\EntityEventStrategy\OnDemand
 */
class OnDemandTest extends VaGovUnitTestBase {

  /**
   * Construct the plugin.
   */
  public function getPlugin() {
    $stringTranslation = $this->getStringTranslationStub();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('string_translation')->willReturn($stringTranslation);

    $loggerProphecy = $this->prophesize(LoggerInterface::class);
    $loggerProphecy->info(Argument::cetera());
    $logger = $loggerProphecy->reveal();
    $loggerChannelFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerChannelFactoryProphecy->get('va_gov_content_release')->willReturn($logger);
    $loggerChannelFactory = $loggerChannelFactoryProphecy->reveal();

    $containerProphecy->get('logger.factory')->willReturn($loggerChannelFactory);
    $container = $containerProphecy->reveal();
    return OnDemand::create($container, [], 'test', []);
  }

  /**
   * Test that the On-Demand strategy plugin can be created.
   *
   * @covers ::create
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(OnDemand::class, $this->getPlugin());
  }

  /**
   * Test shouldTriggerContentRelease().
   *
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::shouldTriggerContentRelease
   * @dataProvider shouldTriggerContentReleaseDataProvider
   */
  public function testShouldTriggerContentRelease(bool $expected) {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $nodeProphecy->shouldTriggerContentRelease()->willReturn($expected);
    $this->assertEquals($expected, $this->getPlugin()->shouldTriggerContentRelease($nodeProphecy->reveal()));
  }

  /**
   * Data provider for testShouldTriggerContentRelease().
   *
   * @return array[]
   *   An array of test data.
   */
  public function shouldTriggerContentReleaseDataProvider() {
    return [
      'true' => [TRUE],
      'false' => [FALSE],
    ];
  }

  /**
   * Test that we can build a "reason" message.
   *
   * @param \Drupal\va_gov_content_types\Entity\VaNodeInterface $node
   *   The node to use for the message.
   * @param string $expected
   *   The expected message.
   *
   * @covers ::getReasonMessage
   * @dataProvider getReasonMessageDataProvider
   */
  public function testGetReasonMessage(VaNodeInterface $node, string $expected) {
    $this->assertStringContainsString($expected, $this->getPlugin()->getReasonMessage($node));
  }

  /**
   * Data provider for testGetReasonMessage().
   *
   * @return array[]
   *   An array of test data.
   */
  public function getReasonMessageDataProvider() {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $nodeProphecy->label()->willReturn('Test Node');
    $nodeProphecy->id()->willReturn('123');
    $nodeProphecy->getEntityTypeId()->willReturn('node');
    $nodeProphecy->getType()->willReturn('va_node');
    $link = $this->getMockBuilder(Link::class)
      ->disableOriginalConstructor()
      ->getMock();
    $link->expects($this->any())
      ->method('toString')
      ->willReturn('https://fake.link/');
    $nodeProphecy->toLink(Argument::cetera())->willReturn($link);
    $nodeProphecy->shouldTriggerContentRelease()->willReturn(TRUE);
    return [
      'va_node' => [
        $nodeProphecy->reveal(),
        'A content release was triggered by a change to <em class="placeholder">va_node</em>: <em class="placeholder">https://fake.link/</em> (node<em class="placeholder">123</em>).',
      ],
    ];
  }

}
