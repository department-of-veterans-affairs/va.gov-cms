<?php

namespace tests\phpunit\va_gov_content_release\unit\Plugin\EntityEventStrategy;

use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_content_release\Plugin\EntityEventStrategy\VerboseFalse;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Verbose FALSE entity event strategy plugin.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Plugin\EntityEventStrategy\VerboseFalse
 */
class VerboseFalseTest extends VaGovUnitTestBase {

  use GeneratePermutationsTrait;

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
    return VerboseFalse::create($container, [], 'test', []);
  }

  /**
   * Test that the Verbose FALSE strategy plugin can be created.
   *
   * @covers ::create
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(VerboseFalse::class, $this->getPlugin());
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
    $nodeProphecy->label()->willReturn('Test Node');
    $nodeProphecy->id()->willReturn('123');
    $nodeProphecy->getEntityTypeId()->willReturn('node');
    $nodeProphecy->getType()->willReturn('va_node');
    $nodeProphecy->getContentReleaseTriggerDetails()->willReturn(['foo' => 'bar']);

    $link = $this->getMockBuilder(Link::class)
      ->disableOriginalConstructor()
      ->getMock();
    $link->expects($this->any())
      ->method('toString')
      ->willReturn('https://fake.link/');
    $nodeProphecy->toLink(Argument::cetera())->willReturn($link);

    $node = $nodeProphecy->reveal();

    $stringTranslation = $this->getStringTranslationStub();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('string_translation')->willReturn($stringTranslation);

    $loggerProphecy = $this->prophesize(LoggerInterface::class);
    $test = $this;
    $loggerProphecy->info(Argument::cetera())->will(function ($args) use ($test, $expected) {
      $expectedText = $expected ? 'would</em> have' : 'would not</em> have';
      $test->assertTrue(str_contains((string) $args[0], $expectedText));
    });
    $logger = $loggerProphecy->reveal();
    $loggerChannelFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerChannelFactoryProphecy->get('va_gov_content_release')->willReturn($logger);
    $loggerChannelFactory = $loggerChannelFactoryProphecy->reveal();

    $containerProphecy->get('logger.factory')->willReturn($loggerChannelFactory);
    $container = $containerProphecy->reveal();
    $plugin = VerboseFalse::create($container, [], 'test', []);

    $this->assertFalse($plugin->shouldTriggerContentRelease($node));
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
        'This should never be reached because the strategy always indicates that no content release should be triggered.',
      ],
    ];
  }

  /**
   * Test getMessage().
   *
   * @covers ::getMessage
   */
  public function testGetMessage() {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $nodeProphecy->label()->willReturn('Test Node');
    $nodeProphecy->id()->willReturn('123');
    $nodeProphecy->getEntityTypeId()->willReturn('node');
    $nodeProphecy->getType()->willReturn('va_node');
    $nodeProphecy->shouldTriggerContentRelease()->willReturn(TRUE);
    $nodeProphecy->getContentReleaseTriggerDetails()->willReturn(['foo' => 'bar']);

    $link = $this->getMockBuilder(Link::class)
      ->disableOriginalConstructor()
      ->getMock();
    $link->expects($this->any())
      ->method('toString')
      ->willReturn('https://fake.link/');
    $nodeProphecy->toLink(Argument::cetera())->willReturn($link);

    $node = $nodeProphecy->reveal();
    $actual = $this->getPlugin()->getMessage($node);
    $expectedTexts = [
      'A content release <em class="placeholder">would</em> have been triggered by a change',
      '<em class="placeholder">va_node</em>',
      '<em class="placeholder">https://fake.link/</em>',
      '(node <em class="placeholder">123</em>)',
      '}).',
    ];
    foreach ($expectedTexts as $expectedText) {
      $this->assertTrue(str_contains($actual, $expectedText), "$actual does not contain $expectedText");
    }
  }

}
