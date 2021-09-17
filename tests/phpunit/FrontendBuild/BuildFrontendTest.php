<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\Service\BuildFrontend;
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


  const FACILITIES = [
    'health_care_local_facility',
    'vet_center',
  ];
  const NON_FACILITIES = [
    'page',
    'banner',
    'full_width_banner_alert',
  ];
  const STATUS_CHANGED = 'status_changed';
  const STATUS_SAME = 'status_same';
  const STATUS_FIELD_NAME = 'field_operating_status_facility';
  const STATUS_FIELD_MORE_INFO_NAME = 'field_operating_status_more_info';

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
    $userProphecy = $this->prophesize(AccountInterface::class);
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
    $currentUser = $userProphecy->reveal();

    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery, $currentUser);

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
    $userProphecy = $this->prophesize(AccountInterface::class);
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
    $currentUser = $userProphecy->reveal();

    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery, $currentUser);

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
    $userProphecy = $this->prophesize(AccountInterface::class);

    $environmentDiscoveryProphecy->shouldTriggerFrontendBuild()->willReturn(FALSE);
    $environmentDiscoveryProphecy->triggerFrontendBuild()->shouldNotBeCalled();

    $nodeProphecy->isPublished()->willReturn(FALSE)->shouldBeCalled();
    $nodeProphecy->getType()->willReturn('fail')->shouldNotBeCalled();
    $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => 'draft'])->shouldBeCalled();
    $node = $nodeProphecy->reveal();

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $currentUser = $userProphecy->reveal();

    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery, $currentUser);

    $buildFrontend->triggerFrontendBuildFromContentSave($node);
  }

  /**
   * Test triggerFrontendBuildFromContentSave()
   *
   * @param bool $isPublished
   *   Value for mock NodeInterface::isPublished().
   * @param string $contentType
   *   Value for mock NodeInterface::getType().
   * @param string|bool $hasOriginal
   *   Whether this node has a previous revision and should have a diff status.
   * @param string $moderationState
   *   Value for mock NodeInterface::get('moderation_state')->value.
   * @param string|null $originalModerationState
   *   Pretend we had an original node with a different moderation state.
   * @param bool $expected
   *   Whether the frontend build should be triggered or not.
   *
   * @dataProvider triggerFrontendBuildFromContentSaveDataProvider
   */
  public function testTriggerFrontendBuildFromContentSave(bool $isPublished, string $contentType, $hasOriginal, string $moderationState, $originalModerationState, bool $expected) {
    $environmentDiscoveryProphecy = $this->prophesize(EnvironmentDiscovery::class);
    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $webBuildStatusProphecy = $this->prophesize(WebBuildStatusInterface::class);
    $nodeProphecy = $this->prophesize(NodeInterface::class);
    $userProphecy = $this->prophesize(AccountInterface::class);
    $linkProphecy = $this->prophesize(Link::class);

    $environmentDiscoveryProphecy->shouldTriggerFrontendBuild()->willReturn(TRUE);
    if ($expected) {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::exact(NULL), Argument::exact(FALSE))->shouldBeCalled();
    }
    else {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::exact(NULL), Argument::exact(FALSE))->shouldNotBeCalled();
    }

    $nodeProphecy->isPublished()->willReturn($isPublished);
    $nodeProphecy->getType()->willReturn($contentType);
    // Nothing special about this node id. It is just used for logging.
    $nodeProphecy->id()->willReturn(5);
    if ($this->isFacility($contentType)) {
      $nodeProphecy->hasField(self::STATUS_FIELD_NAME)->willReturn(TRUE);
      $nodeProphecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(TRUE);
      $nodeProphecy->get(self::STATUS_FIELD_NAME)->willReturn((object) ['value' => 'a']);
      $nodeProphecy->get(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn((object) ['value' => 'a']);
    }
    else {
      $nodeProphecy->hasField(self::STATUS_FIELD_NAME)->willReturn(FALSE);
      $nodeProphecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(FALSE);
    }

    $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => $moderationState])->shouldBeCalled();
    $linkProphecy->toString()->willReturn('<a href="https://sample.com/a/test/path"></a>');
    $link = $linkProphecy->reveal();
    $nodeProphecy->toLink(Argument::type('NULL'), Argument::type('string'), Argument::type('array'))->willReturn($link);
    $node = $nodeProphecy->reveal();
    if ($hasOriginal) {
      $node2Prophecy = $this->prophesize(NodeInterface::class);
      $node2Prophecy->isPublished()->willReturn($originalModerationState === 'published');

      $node2Prophecy->get('moderation_state')->willReturn((object) ['value' => $originalModerationState]);
      if ($this->isFacility($contentType)) {
        $node2Prophecy->hasField(self::STATUS_FIELD_NAME)->willReturn(TRUE);
        $node2Prophecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(TRUE);
        if ($hasOriginal === self::STATUS_SAME) {
          $node2Prophecy->get(self::STATUS_FIELD_NAME)->willReturn((object) ['value' => 'a']);
          $node2Prophecy->get(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn((object) ['value' => 'a']);
        }
        elseif ($hasOriginal === self::STATUS_CHANGED) {
          $node2Prophecy->get(self::STATUS_FIELD_NAME)->willReturn((object) ['value' => 'b']);
          $node2Prophecy->get(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn((object) ['value' => 'b']);
        }

      }
      else {
        $node2Prophecy->hasField(self::STATUS_FIELD_NAME)->willReturn(FALSE);
        $node2Prophecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(FALSE);

      }

      $node->original = $node2Prophecy->reveal();
    }

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $userProphecy->getAccountName()->willReturn('George De Jungle');
    $currentUser = $userProphecy->reveal();

    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery, $currentUser);

    $buildFrontend->triggerFrontendBuildFromContentSave($node);
  }

  /**
   * Data provider for ::testTriggerFrontendBuildFromContentSave().
   */
  public function triggerFrontendBuildFromContentSaveDataProvider() {
    $combinations = [
      'content_type' => array_merge(self::FACILITIES, self::NON_FACILITIES),
      'has_original' => [
        self::STATUS_CHANGED,
        self::STATUS_SAME,
        FALSE,
      ],
      'moderation_state' => [
        'published',
        'draft',
        'archived',
      ],
      'original_moderation_state' => [
        'published',
        'draft',
        'archived',
        NULL,
      ],
    ];
    $permutations = $this->generatePermutations($combinations);
    $result = [];
    foreach ($permutations as $permutation) {
      $isFacility = $this->isFacility($permutation['content_type']);
      $is_published = $permutation['moderation_state'] === 'published';

      switch (TRUE) {
        case $permutation['content_type'] === 'page':
          // Page should never trigger a content release.
          $permutation['expected'] = FALSE;
          break;

        case (!$isFacility && $is_published):
          // Normal publish of revision of any non-facility.
        case (($permutation['original_moderation_state'] === 'published') && ($permutation['moderation_state'] === 'archived')):
          // Archive of published node.
        case $isFacility && $is_published && ($permutation['has_original'] === self::STATUS_CHANGED):
          // Facility revision published and has a status change.
        case $isFacility && $is_published && ($permutation['original_moderation_state'] !== 'published'):
          // Facility revision newly published.
          $permutation['expected'] = TRUE;
          break;

        default:
          $permutation['expected'] = FALSE;
      }

      // Do not include impossible permutation where has_original is FALSE but
      // original_moderation_state has a value.
      if (empty($permutation['has_original']) && !empty($permutation['original_moderation_state'])) {
        continue;
      }
      // Do not include impossible permutation where has_original is TRUE but
      // original_moderation_state has no value.
      if (!empty($permutation['has_original']) && empty($permutation['original_moderation_state'])) {
        continue;
      }
      $arguments = [
        $is_published,
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

  /**
   * Checks to see if the content type is a facility.
   *
   * @param string $node_type
   *   The type/bundle of the node.
   *
   * @return bool
   *   TRUE if the bundle is a facility, FALSE otherwise.
   */
  protected function isFacility($node_type): bool {
    return in_array($node_type, self::FACILITIES);

  }

}
