<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_build_trigger\Service\BuildFrontend;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Prophecy\Argument;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the BuildFrontend class.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Service\BuildFrontend
 */
class BuildFrontendTest extends ExistingSiteBase {

  use GeneratePermutationsTrait;

  /**
   * Test triggerFrontendBuild()
   *
   * @param string $env
   *   Environment name, like 'lando', 'prod', etc.
   * @param bool $permitted
   *   Indicates whether the specified environment name is considered valid.
   *
   * @dataProvider triggerFrontendBuildDataProvider
   */
  public function testTriggerFrontendBuild(string $env, bool $permitted) {
    $realPermitted = $this->container->get('plugin.manager.va_gov.environment')->hasDefinition($env);
    $this->assertEquals($permitted, $realPermitted);

    $environmentDiscoveryProphecy = $this->prophesize(EnvironmentDiscovery::class);

    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $webBuildStatusProphecy = $this->prophesize(WebBuildStatusInterface::class);
    $webBuildStatusProphecy->enableWebBuildStatus()->shouldNotBeCalled();
    if ($permitted) {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::any(), Argument::any())->shouldBeCalled();
      $loggerProphecy->warning(Argument::any())->shouldNotBeCalled();
      $messengerProphecy->addWarning(Argument::any())->shouldNotBeCalled();
      $webBuildStatusProphecy->disableWebBuildStatus()->shouldNotBeCalled();
    }
    else {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::any(), Argument::any())->willThrow(PluginException::class);
      $loggerProphecy->warning(Argument::type(TranslatableMarkup::class))->shouldBeCalled();
      $messengerProphecy->addWarning(Argument::type(TranslatableMarkup::class))->shouldBeCalled();
      $webBuildStatusProphecy->disableWebBuildStatus()->shouldBeCalled();
    }

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();

    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery);

    $buildFrontend->triggerFrontendBuild('no', FALSE);
  }

  /**
   * Data provider for ::testTriggerFrontendBuild().
   */
  public function triggerFrontendBuildDataProvider() {
    return [
      [
        'lando',
        TRUE,
      ],
      [
        'tugboat',
        TRUE,
      ],
      [
        'brd',
        TRUE,
      ],
      [
        'test',
        FALSE,
      ],
    ];
  }

  /**
   * Test setPendingState()
   *
   * @dataProvider setPendingStateDataProvider
   */
  public function testSetPendingState(bool $status) {
    $environmentDiscoveryProphecy = $this->prophesize(EnvironmentDiscovery::class);

    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $webBuildStatusProphecy = $this->prophesize(WebBuildStatusInterface::class);
    if ($status) {
      $webBuildStatusProphecy->enableWebBuildStatus()->shouldBeCalled();
      $webBuildStatusProphecy->disableWebBuildStatus()->shouldNotBeCalled();
    }
    else {
      $webBuildStatusProphecy->enableWebBuildStatus()->shouldNotBeCalled();
      $webBuildStatusProphecy->disableWebBuildStatus()->shouldBeCalled();
    }

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();

    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();

    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery);

    $buildFrontend->setPendingState($status);
  }

  /**
   * Data provider for ::testSetPendingState().
   */
  public function setPendingStateDataProvider() {
    return [
      [
        TRUE,
      ],
      [
        FALSE,
      ],
    ];
  }

  /**
   * Test triggerFrontendBuildFromContentSave()'s environment-based blocking.
   */
  public function testTriggerFrontendBuildFromContentSave1() {
    $environmentDiscoveryProphecy = $this->prophesize(EnvironmentDiscovery::class);
    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $webBuildStatusProphecy = $this->prophesize(WebBuildStatusInterface::class);
    $nodeProphecy = $this->prophesize(NodeInterface::class);

    $environmentDiscoveryProphecy->shouldTriggerFrontendBuild()->willReturn(FALSE);
    $environmentDiscoveryProphecy->triggerFrontendBuild()->shouldNotBeCalled();

    $nodeProphecy->isPublished()->willReturn(FALSE)->shouldNotBeCalled();
    $nodeProphecy->getType()->willReturn('fail')->shouldNotBeCalled();
    $node = $nodeProphecy->reveal();

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();

    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery);

    $buildFrontend->triggerFrontendBuildFromContentSave($node);
  }

  /**
   * Test triggerFrontendBuildFromContentSave()
   *
   * @param bool $isPublished
   *   Value for mock NodeInterface::isPublished().
   * @param string $contentType
   *   Value for mock NodeInterface::getType().
   * @param bool $hasOriginal
   *   Whether this node has a previous revision.
   * @param string $moderationState
   *   Value for mock NodeInterface::get('moderation_state')->value.
   * @param string $originalModerationState
   *   Pretend we had an original node with a different moderation state.
   * @param bool $expected
   *   Whether the frontend build should be triggered or not.
   *
   * @dataProvider triggerFrontendBuildFromContentSaveDataProvider
   */
  public function testTriggerFrontendBuildFromContentSave(bool $isPublished, string $contentType, bool $hasOriginal, string $moderationState, string $originalModerationState, bool $expected) {
    $environmentDiscoveryProphecy = $this->prophesize(EnvironmentDiscovery::class);
    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $webBuildStatusProphecy = $this->prophesize(WebBuildStatusInterface::class);
    $nodeProphecy = $this->prophesize(NodeInterface::class);

    $environmentDiscoveryProphecy->shouldTriggerFrontendBuild()->willReturn(TRUE);
    if ($expected) {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::exact(NULL), Argument::exact(FALSE))->shouldBeCalled();
    }
    else {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::exact(NULL), Argument::exact(FALSE))->shouldNotBeCalled();
    }

    $nodeProphecy->isPublished()->willReturn($isPublished)->shouldBeCalled();
    $nodeProphecy->getType()->willReturn($contentType)->shouldBeCalled();
    $nodeProphecy->hasField(Argument::type('string'))->willReturn(FALSE);
    $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => $moderationState])->shouldBeCalled();

    $node = $nodeProphecy->reveal();
    if ($hasOriginal) {
      $node2Prophecy = $this->prophesize(NodeInterface::class);
      $node2Prophecy->hasField(Argument::type('string'))->willReturn(FALSE);
      $node2Prophecy->get('moderation_state')->willReturn((object) ['value' => $originalModerationState])->shouldBeCalled();
      $node->original = $node2Prophecy->reveal();
    }

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();

    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery);

    $buildFrontend->triggerFrontendBuildFromContentSave($node);
  }

  /**
   * Data provider for ::testTriggerFrontendBuildFromContentSave().
   */
  public function triggerFrontendBuildFromContentSaveDataProvider() {
    $combinations = [
      'content_type' => [
        'page',
        'full_width_banner_alert',
        'health_care_local_facility',
      ],
      'has_original' => [
        TRUE,
        FALSE,
      ],
      'moderation_state' => [
        'published',
        'archived',
      ],
      'original_moderation_state' => [
        'published',
        'archived',
      ],
    ];
    $permutations = $this->generatePermutations($combinations);
    $result = [];
    foreach ($permutations as $permutation) {
      $isFacility = $permutation['content_type'] === 'health_care_local_facility';
      $permutation['expected'] = TRUE;
      $permutation['expected'] &= $permutation['content_type'] !== 'page';
      $permutation['expected'] &= $permutation['moderation_state'] === 'published';
      $permutation['expected'] &= !$isFacility || !$permutation['has_original'] || $permutation['original_moderation_state'] !== 'published';
      $arguments = [
        $permutation['moderation_state'] === 'published',
        $permutation['content_type'],
        $permutation['has_original'],
        $permutation['moderation_state'],
        $permutation['original_moderation_state'],
        (bool) $permutation['expected'],
      ];
      $result[] = $arguments;
    }
    return $result;
  }

}
