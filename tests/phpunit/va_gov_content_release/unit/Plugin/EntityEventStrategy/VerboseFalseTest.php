<?php

namespace tests\phpunit\va_gov_content_release\unit\Plugin\EntityEventStrategy;

use Drupal\Core\Link;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_release\Plugin\EntityEventStrategy\VerboseFalse;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;

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
    $loggerProphecy->info(Argument::cetera())->willReturn(NULL);
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

    $stringTranslation = $this->getStringTranslationStub();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('string_translation')->willReturn($stringTranslation);

    $loggerProphecy = $this->prophesize(LoggerInterface::class);
    $test = $this;
    $loggerProphecy->info(Argument::cetera())->will(function ($args) use ($test, $expected) {
      $expectedText = $expected ? 'would have' : 'would not have';
      $test->assertTrue(str_contains((string) $args[0], $expectedText));
    });
    $logger = $loggerProphecy->reveal();
    $loggerChannelFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerChannelFactoryProphecy->get('va_gov_content_release')->willReturn($logger);
    $loggerChannelFactory = $loggerChannelFactoryProphecy->reveal();

    $containerProphecy->get('logger.factory')->willReturn($loggerChannelFactory);
    $container = $containerProphecy->reveal();
    $plugin = VerboseFalse::create($container, [], 'test', []);

    $this->assertFalse($plugin->shouldTriggerContentRelease($nodeProphecy->reveal()));
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
   * Test getNodeDetails().
   *
   * This is a very long test. Don't enable it (by adding the `test` prefix)
   * unless you need to debug something.
   *
   * @param bool $isFacility
   *   Whether the node is a facility.
   * @param bool $isModerated
   *   Whether the node is moderated.
   * @param bool $hasOriginal
   *   Whether the node has an original.
   * @param bool $didChangeOperatingStatus
   *   Whether the node changed operating status.
   * @param bool $alwaysTriggersContentRelease
   *   Whether the node always triggers a content release.
   * @param bool $isModeratedAndPublished
   *   Whether the node is moderated and published.
   * @param bool $isModeratedAndTransitionedFromPublishedToArchived
   *   Whether the node is moderated and transitioned from published to
   *   archived.
   * @param bool $isUnmoderatedAndPublished
   *   Whether the node is unmoderated and published.
   * @param bool $isUnmoderatedAndWasPreviouslyPublished
   *   Whether the node is unmoderated and was previously published.
   * @param bool $didTransitionFromPublishedToArchived
   *   Whether the node transitioned from published to archived.
   * @param bool $isCmPublished
   *   Whether the node is published under content moderation.
   * @param bool $isPublished
   *   Whether the node is published.
   * @param bool $isArchived
   *   Whether the node is archived.
   * @param bool $isDraft
   *   Whether the node is a draft.
   * @param bool $wasPublished
   *   Whether the node was published.
   * @param array $expected
   *   The expected result.
   *
   * @covers ::getNodeDetails
   * @dataProvider getNodeDetailsDataProvider
   */
  public function getNodeDetails(
    bool $isFacility,
    bool $isModerated,
    bool $hasOriginal,
    bool $didChangeOperatingStatus,
    bool $alwaysTriggersContentRelease,
    bool $isModeratedAndPublished,
    bool $isModeratedAndTransitionedFromPublishedToArchived,
    bool $isUnmoderatedAndPublished,
    bool $isUnmoderatedAndWasPreviouslyPublished,
    bool $didTransitionFromPublishedToArchived,
    bool $isCmPublished,
    bool $isPublished,
    bool $isArchived,
    bool $isDraft,
    bool $wasPublished,
    array $expected
  ) {
    $nodeProphecy = $this->prophesize(VaNodeInterface::class);
    $nodeProphecy->label()->willReturn('Test Node');
    $nodeProphecy->id()->willReturn('123');
    $nodeProphecy->getEntityTypeId()->willReturn('node');
    $nodeProphecy->getType()->willReturn('va_node');
    $nodeProphecy->isFacility()->willReturn($isFacility);
    $nodeProphecy->isModerated()->willReturn($isModerated);
    $nodeProphecy->hasOriginal()->willReturn($hasOriginal);
    $nodeProphecy->didChangeOperatingStatus()->willReturn($didChangeOperatingStatus);
    $nodeProphecy->alwaysTriggersContentRelease()->willReturn($alwaysTriggersContentRelease);
    $nodeProphecy->isModeratedAndPublished()->willReturn($isModeratedAndPublished);
    $nodeProphecy->isModeratedAndTransitionedFromPublishedToArchived()->willReturn($isModeratedAndTransitionedFromPublishedToArchived);
    $nodeProphecy->isUnmoderatedAndPublished()->willReturn($isUnmoderatedAndPublished);
    $nodeProphecy->isUnmoderatedAndWasPreviouslyPublished()->willReturn($isUnmoderatedAndWasPreviouslyPublished);
    $nodeProphecy->didTransitionFromPublishedToArchived()->willReturn($didTransitionFromPublishedToArchived);
    $nodeProphecy->isCmPublished()->willReturn($isCmPublished);
    $nodeProphecy->isPublished()->willReturn($isPublished);
    $nodeProphecy->isArchived()->willReturn($isArchived);
    $nodeProphecy->isDraft()->willReturn($isDraft);
    $nodeProphecy->wasPublished()->willReturn($wasPublished);
    $node = $nodeProphecy->reveal();
    $this->assertEquals($expected, $this->getPlugin()->getNodeDetails($node));
  }

  /**
   * Data provider for testGetNodeDetails().
   *
   * @return array[]
   *   An array of test data.
   */
  public function getNodeDetailsDataProvider() {
    $combinations = [
      'isFacility' => [TRUE, FALSE],
      'isModerated' => [TRUE, FALSE],
      'hasOriginal' => [TRUE, FALSE],
      'didChangeOperatingStatus' => [TRUE, FALSE],
      'alwaysTriggersContentRelease' => [TRUE, FALSE],
      'isModeratedAndPublished' => [TRUE, FALSE],
      'isModeratedAndTransitionedFromPublishedToArchived' => [TRUE, FALSE],
      'isUnmoderatedAndPublished' => [TRUE, FALSE],
      'isUnmoderatedAndWasPreviouslyPublished' => [TRUE, FALSE],
      'didTransitionFromPublishedToArchived' => [TRUE, FALSE],
      'isCmPublished' => [TRUE, FALSE],
      'isPublished' => [TRUE, FALSE],
      'isArchived' => [TRUE, FALSE],
      'isDraft' => [TRUE, FALSE],
      'wasPublished' => [TRUE, FALSE],
    ];
    $permutations = $this->generatePermutations($combinations);
    foreach ($permutations as $permutation) {
      $expected = [];

      foreach ($permutation as $key => $value) {
        switch ($key) {

          case 'didChangeOperatingStatus':
            $expected['didChangeOperatingStatus'] = $permutation['isFacility'] && $value;
            break;

          case 'didTransitionFromPublishedToArchived':
            $expected['didTransitionFromPublishedToArchived'] = $permutation['isModerated'] && $permutation['hasOriginal'] && $value;
            break;

          case 'isCmPublished':
            $expected['isCmPublished'] = $permutation['isModerated'] && $value;
            break;

          case 'isArchived':
            $expected['isArchived'] = $permutation['isModerated'] && $value;
            break;

          case 'isDraft':
            $expected['isDraft'] = $permutation['isModerated'] && $value;
            break;

          case 'wasPublished':
            $expected['wasPublished'] = $permutation['isModerated'] && $permutation['hasOriginal'] && $value;
            break;

          default:
            $expected[$key] = $value;
            break;

        }
      }
      yield array_merge($permutation, ['expected' => $expected]);
    }
    return $permutations;
  }

}
