<?php

namespace tests\phpunit\Content;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\post_api\Service\AddToQueue;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_post_api\Service\PostFacilityStatus;
use Prophecy\Argument;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the Facility status queueing for push to Lighthouse.
 *
 * @coversDefaultClass \Drupal\va_gov_post_api\Service\PostFacilityStatus
 */
class FacilityStatusQueueTest extends ExistingSiteBase {

  use GeneratePermutationsTrait;

  const FACILITIES_WITH_STATUS = [
    'health_care_local_facility',
    'nca_facility',
    'vba_facility',
    'vet_center',
    'vet_center_outstation',
  ];

  const NON_STATUS_CONTENT = [
    'page',
    'vet_center_cap',
    'vet_center_mobile_vet_center',
  ];

  const STATUS_CHANGED = 'status_changed';
  const STATUS_SAME = 'status_same';
  const STATUS_FIELD_NAME = 'field_operating_status_facility';
  const STATUS_FIELD_MORE_INFO_NAME = 'field_operating_status_more_info';
  const FACILITY_ID = 'field_facility_locator_api_id';
  const STATE_ARCHIVED = 'archived';
  const STATE_DRAFT = 'draft';
  const STATE_PUBLISHED = 'published';

  /**
   * Test queueFacilityStatus()
   *
   * @param string $contentType
   *   Value for mock NodeInterface::getType().
   * @param string|bool $statusInfo
   *   Whether this node has a change to status/status info.
   * @param bool $isPublished
   *   Value for mock NodeInterface::isPublished().
   * @param string|null $originalModerationState
   *   Pretend we had an original node with a different moderation state.
   * @param string $moderationState
   *   Value for mock NodeInterface::get('moderation_state')->value.
   * @param bool $bypassDataCheck
   *   Whether the system should bypass data checks on status fields.
   * @param bool $expected
   *   Whether the frontend build should be triggered or not.
   *
   * @dataProvider triggerFacilityStatusPushFromContentSaveDataProvider
   */
  public function testFacilityStatusPushFromContentSave(string $contentType, $statusInfo, bool $isPublished, $originalModerationState, string $moderationState, bool $bypassDataCheck, bool $expected) {
    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $nodeProphecy = $this->prophesize(NodeInterface::class);
    $entityTypeManagerProphecy = $this->prophesize(EntityTypeManagerInterface::class);
    $addToQueueProphecy = $this->prophesize(AddToQueue::class);
    $configFactoryProphecy = $this->prophesize(ConfigFactoryInterface::class);
    $entityStorageProphecy = $this->prophesize(EntityStorageInterface::class);
    $nodeTypeProphecy = $this->prophesize(NodeType::class);

    // Establish methods and return values.
    $nodeProphecy->isPublished()->willReturn($isPublished);
    // There is nothing special about this id, it just has to return a number.
    $nodeProphecy->id()->willReturn(5);
    $nodeProphecy->bundle()->willReturn($contentType);
    $nodeTypeProphecy->label()->willReturn('facility label');
    $nodeType = $nodeTypeProphecy->reveal();
    $nodeProphecy->get('type')->willReturn((object) ['entity' => $nodeType]);
    $nodeProphecy->getTitle()->willReturn('A title');
    $nodeProphecy->isNew()->willReturn($originalModerationState === NULL);

    if ($this->isFacilityWithStatus($contentType)) {
      $nodeProphecy->hasField(self::STATUS_FIELD_NAME)->willReturn(TRUE);
      $nodeProphecy->get(self::STATUS_FIELD_NAME)->willReturn((object) ['value' => 'a']);
      $nodeProphecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(TRUE);
      $nodeProphecy->get(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn((object) ['value' => 'a']);
      $nodeProphecy->hasField(self::FACILITY_ID)->willReturn(TRUE);
      $nodeProphecy->get(self::FACILITY_ID)->willReturn((object) ['value' => 'vha_000']);
      $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => $moderationState])->shouldBeCalled();
    }
    else {
      $nodeProphecy->hasField(self::STATUS_FIELD_NAME)->willReturn(FALSE);
      $nodeProphecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(FALSE);
      $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => $moderationState])->shouldNotBeCalled();
    }

    $node = $nodeProphecy->reveal();
    if ($originalModerationState) {
      // Mock the original instance of the node.
      $nodeOriginalProphecy = $this->prophesize(NodeInterface::class);
      $nodeOriginalProphecy->isPublished()->willReturn($originalModerationState === self::STATE_PUBLISHED);

      $nodeOriginalProphecy->get('moderation_state')->willReturn((object) ['value' => $originalModerationState]);
      if ($this->isFacilityWithStatus($contentType)) {
        $nodeOriginalProphecy->hasField(self::STATUS_FIELD_NAME)->willReturn(TRUE);
        $nodeOriginalProphecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(TRUE);
        if ($statusInfo === self::STATUS_SAME) {
          $nodeOriginalProphecy->get(self::STATUS_FIELD_NAME)->willReturn((object) ['value' => 'a']);
          $nodeOriginalProphecy->get(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn((object) ['value' => 'a']);
        }
        elseif ($statusInfo === self::STATUS_CHANGED) {
          $nodeOriginalProphecy->get(self::STATUS_FIELD_NAME)->willReturn((object) ['value' => 'b']);
          $nodeOriginalProphecy->get(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn((object) ['value' => 'b']);
        }
      }
      else {
        $nodeOriginalProphecy->hasField(self::STATUS_FIELD_NAME)->willReturn(FALSE);
        $nodeOriginalProphecy->hasField(self::STATUS_FIELD_MORE_INFO_NAME)->willReturn(FALSE);
      }
      $nodeDefaultProphecy = clone $nodeOriginalProphecy;
      $node->original = $nodeOriginalProphecy->reveal();
      if ($isPublished) {
        // Set some values for the default revision.
        $nodeDefaultProphecy->isPublished()->willReturn(TRUE);
        $nodeDefaultProphecy->get('moderation_state')->willReturn((object) ['value' => self::STATE_PUBLISHED]);
      }
      else {
        $nodeDefaultProphecy->isPublished()->willReturn(FALSE);
        $nodeDefaultProphecy->get('moderation_state')->willReturn((object) ['value' => self::STATE_DRAFT]);
      }
      $entityStorageProphecy->load(5)->willReturn($nodeDefaultProphecy->reveal());
      $entityStorage = $entityStorageProphecy->reveal();
      $entityTypeManagerProphecy->getStorage('node')->willReturn($entityStorage);
    }
    else {
      $entityStorageProphecy->load(5)->willReturn($node);
      $entityStorage = $entityStorageProphecy->reveal();
      $entityTypeManagerProphecy->getStorage('node')->willReturn($entityStorage);
    }

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $immutableConfigProphecy = $this->prophesize(ImmutableConfig::class);
    $immutableConfigProphecy->get('bypass_data_check')->willReturn($bypassDataCheck);
    $immutableConfig = $immutableConfigProphecy->reveal();
    $configFactoryProphecy->get('va_gov_post_api.settings')->willReturn($immutableConfig);
    $configFactory = $configFactoryProphecy->reveal();

    $entityTypeManager = $entityTypeManagerProphecy->reveal();
    // We are not going to actually queue anything.
    $addToQueueProphecy->addToQueue(Argument::type('array'), Argument::type('bool'))->willReturn('void');
    $addToQueueProphecy->buildQueueItemData(Argument::type('array'))->willReturn('void');
    $addToQueue = $addToQueueProphecy->reveal();

    $queueFacility = new PostFacilityStatus($configFactory, $entityTypeManager, $loggerFactory, $messenger, $addToQueue);
    $success = $queueFacility->queueFacilityStatus($node);

    if ($expected) {
      self::assertTrue(((bool) $success), 'Expected status info to be queued.');
    }
    else {
      self::assertFalse(((bool) $success), "Status info should not have been queued.");
    }
  }

  /**
   * Data provider for ::testFacilityStatusPushFromContentSave().
   */
  public function triggerFacilityStatusPushFromContentSaveDataProvider() {
    $combinations = [
      'content_type' => array_merge(self::FACILITIES_WITH_STATUS, self::NON_STATUS_CONTENT),
      'published' => [
        TRUE,
        FALSE,
      ],
      'status_info' => [
        self::STATUS_CHANGED,
        self::STATUS_SAME,
        FALSE,
      ],
      'moderation_state' => [
        self::STATE_PUBLISHED,
        self::STATE_DRAFT,
        self::STATE_ARCHIVED,
      ],
      'original_moderation_state' => [
        self::STATE_PUBLISHED,
        self::STATE_DRAFT,
        self::STATE_ARCHIVED,
        NULL,
      ],
      'bypass_data_check' => [
        TRUE,
        FALSE,
      ],
    ];
    $permutations = $this->generatePermutations($combinations);
    $result = [];
    foreach ($permutations as $permutation) {
      $isFacilityWithStatus = $this->isFacilityWithStatus($permutation['content_type']);
      $default_rev_is_published = $permutation['published'];
      $is_a_publish = $permutation['moderation_state'] === self::STATE_PUBLISHED;
      $status_info_changed = $permutation['status_info'] === self::STATUS_CHANGED;

      switch (TRUE) {
        case empty($permutation['status_info']) && !empty($permutation['original_moderation_state']):
          // Impossible permutation: status_info is FALSE but
          // original_moderation_state has a value.
        case ($permutation['status_info']) && empty($permutation['original_moderation_state']):
          // Impossible permutation: status_info is changed or same but
          // original_moderation_state has no value.
        case ($permutation['original_moderation_state'] === self::STATE_ARCHIVED) && ($permutation['moderation_state'] === self::STATE_ARCHIVED):
          // Impossible permutation: previous and current moderation states
          // are both archived.  The system doesn't allow that.
          // Do not include impossible permutations.
        case !$default_rev_is_published && $permutation['original_moderation_state'] === self::STATE_PUBLISHED:
          // Impossible permutation: not published and former state published.
        case $default_rev_is_published && $permutation['original_moderation_state'] === self::STATE_ARCHIVED:
          // Impossible permutation:published and former state of archived.
        case $default_rev_is_published && $permutation['original_moderation_state'] === NULL:
          // Impossible permutation:Can't be published and no former state.
          continue 2;

        case !$isFacilityWithStatus:
          // These should never queue a status.
        case !$default_rev_is_published && $permutation['original_moderation_state'] === self::STATE_DRAFT && $is_a_publish && !$status_info_changed && !$permutation['bypass_data_check']:
          // Statuses are pushed for unpublished already, so no need to push
          // data that has not changed.
        case ($permutation['status_info'] === self::STATUS_SAME) && !$permutation['bypass_data_check']:
          // Status info did not change and it is not a force.
          $permutation['expected'] = FALSE;
          break;

        case ($is_a_publish && !$default_rev_is_published && $status_info_changed):
          // Normal new publish of revision of any facility with status.
        case (!$default_rev_is_published && !$permutation['original_moderation_state']):
          // A new save with no prior state.
        case (!$default_rev_is_published && $status_info_changed):
          // All non-published facilities with status changes are pushed.
        case (($permutation['original_moderation_state'] === self::STATE_PUBLISHED) && ($permutation['moderation_state'] === self::STATE_ARCHIVED)):
          // Archive of published node.
        case $permutation['moderation_state'] === self::STATE_ARCHIVED && $status_info_changed:
          // Any archive with a status change.
        case $default_rev_is_published && $is_a_publish && $status_info_changed:
          // Facility revision published and has a status change.
        case $permutation['bypass_data_check']:
          // This is a force queueing.
          $permutation['expected'] = TRUE;
          break;

        default:
          $permutation['expected'] = FALSE;
          break;
      }

      $arguments = [
        $permutation['content_type'],
        $permutation['status_info'],
        $default_rev_is_published,
        $permutation['original_moderation_state'],
        $permutation['moderation_state'],
        $permutation['bypass_data_check'],
        (bool) $permutation['expected'],
      ];
      $result[] = $arguments;
    }
    return $result;
  }

  /**
   * Checks to see if the content type is a facility that has status info.
   *
   * @param string $node_type
   *   The type/bundle of the node.
   *
   * @return bool
   *   TRUE if the bundle is a facility, FALSE otherwise.
   */
  protected function isFacilityWithStatus($node_type): bool {
    return in_array($node_type, self::FACILITIES_WITH_STATUS);
  }

}
