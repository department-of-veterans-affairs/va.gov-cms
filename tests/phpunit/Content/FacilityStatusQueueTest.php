<?php

namespace tests\phpunit\Content;

use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_facilities\FacilityOps;
use Drupal\va_gov_post_api\Service\PostFacilityStatus;
use Drupal\va_gov_post_api\Service\PostFacilityWithoutStatus;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Facility status queueing for push to Lighthouse.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_post_api\Service\PostFacilityStatus
 */
class FacilityStatusQueueTest extends VaGovExistingSiteBase {

  use GeneratePermutationsTrait;

  const SECTION_FIELD_NAME = 'field_administration';
  const FACILITY_ID = 'field_facility_locator_api_id';
  const SYSTEM = 'field_region_page';
  const STATE_ARCHIVED = 'archived';
  const STATE_DRAFT = 'draft';
  const STATE_PUBLISHED = 'published';

  /**
   * Test queueFacilityStatus()
   *
   * @param string $contentType
   *   Value for mock NodeInterface::getType().
   * @param bool $isPublished
   *   Value for mock NodeInterface::isPublished().
   * @param string|null $originalModerationState
   *   Pretend we had an original node with a different moderation state.
   * @param string $moderationState
   *   Value for mock NodeInterface::get('moderation_state')->value.
   * @param bool $expected
   *   Whether the frontend build should be triggered or not.
   *
   * @dataProvider triggerFacilityStatusPushFromContentSaveDataProvider
   */
  public function testFacilityStatusPushFromContentSave(string $contentType, bool $isPublished, $originalModerationState, string $moderationState, bool $expected) {
    $nodeProphecy = $this->prophesize(NodeInterface::class);
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

    if (FacilityOps::isBundleFacilityWithStatus($contentType)) {
      $nodeProphecy->hasField(self::SECTION_FIELD_NAME)->willReturn(TRUE);
      $nodeProphecy->get(self::SECTION_FIELD_NAME)->willReturn((object) ['target_id' => '1']);
      $nodeProphecy->hasField(self::FACILITY_ID)->willReturn(TRUE);
      $nodeProphecy->hasField(self::SYSTEM)->willReturn(NULL);
      $nodeProphecy->get(self::FACILITY_ID)->willReturn((object) ['value' => 'vha_000']);
      $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => $moderationState]);
    }
    else {
      $nodeProphecy->get('moderation_state')->willReturn((object) ['value' => $moderationState]);
    }

    $node = $nodeProphecy->reveal();
    if ($originalModerationState) {
      // Mock the original instance of the node.
      $nodeOriginalProphecy = $this->prophesize(NodeInterface::class);
      $nodeOriginalProphecy->isPublished()->willReturn($originalModerationState === self::STATE_PUBLISHED);

      $nodeOriginalProphecy->get('moderation_state')->willReturn((object) ['value' => $originalModerationState]);
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
    }

    // Instead of testing the full post, for the variations in the test
    // we just need to see if it would push it, not the contents of the push.
    if (PostFacilityStatus::isPushAble($node)) {
      $success = PostFacilityStatus::shouldPush($node);
    }
    elseif (PostFacilityWithoutStatus::isPushAble($node)) {
      $success = PostFacilityWithoutStatus::shouldPush($node);
    }
    else {
      $success = FALSE;
    }

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
      'content_type' => FacilityOps::getFacilityTypes(),
      'published' => [
        TRUE,
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
    ];
    $permutations = $this->generatePermutations($combinations);
    $result = [];
    foreach ($permutations as $permutation) {
      $default_rev_is_published = $permutation['published'];
      $is_a_publish = $permutation['moderation_state'] === self::STATE_PUBLISHED;

      switch (TRUE) {
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
        case ($permutation['content_type'] !== 'health_care_local_facility'):
          continue 2;

        case $permutation['content_type'] === 'vet_center_cap':
          // CAPs are handled by a separate push so bypassing for now.
          $permutation['expected'] = FALSE;
          break;

        case ($is_a_publish && !$default_rev_is_published):
          // Normal new publish of revision of any facility.
        case (!$default_rev_is_published && !$permutation['original_moderation_state']):
          // A new save with no prior state.
        case (!$default_rev_is_published && !$is_a_publish):
          // All non-published facilities with status changes are pushed.
        case (($permutation['original_moderation_state'] === self::STATE_PUBLISHED) && ($permutation['moderation_state'] === self::STATE_ARCHIVED)):
          // Archive of published node.
        case $permutation['moderation_state'] === self::STATE_ARCHIVED:
          // Any archive.
        case $default_rev_is_published && $is_a_publish:
          // Facility revision published.
        case $permutation['original_moderation_state'] === NULL:
          // Initial save of node.  Needs to push to set URl.
          $permutation['expected'] = TRUE;
          break;

        default:
          $permutation['expected'] = FALSE;
          break;
      }

      $arguments = [
        $permutation['content_type'],
        $default_rev_is_published,
        $permutation['original_moderation_state'],
        $permutation['moderation_state'],
        (bool) $permutation['expected'],
      ];
      $result[] = $arguments;
    }
    return $result;
  }

}
