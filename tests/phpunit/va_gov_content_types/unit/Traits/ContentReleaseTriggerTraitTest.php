<?php

namespace tests\phpunit\va_gov_content_types\unit\Traits;

use Drupal\Tests\Traits\Core\GeneratePermutationsTrait;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_types\Interfaces\ContentModerationInterface;
use Drupal\va_gov_content_types\Traits\ContentReleaseTriggerTrait;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the ContentReleaseTriggerTrait trait.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_types\Traits\ContentReleaseTriggerTrait
 */
class ContentReleaseTriggerTraitTest extends VaGovUnitTestBase {

  use GeneratePermutationsTrait;

  /**
   * Test the hasTriggeringChanges() method.
   *
   * @param bool $isModerated
   *   Whether the node is moderated.
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
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::hasTriggeringChanges
   * @dataProvider hasTriggeringChangesDataProvider
   */
  public function testHasTriggeringChanges(
    bool $isModerated,
    bool $isCmPublished,
    bool $isPublished,
    bool $isArchived,
    bool $isDraft,
    bool $wasPublished,
    bool $expected
  ) {
    $node = $this->getMockForTrait(ContentReleaseTriggerTrait::class);
    $original = $this->getMockBuilder(VaNodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $original->expects($this->any())
      ->method('isPublished')
      ->willReturn($wasPublished);

    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $node->expects($this->any())->method('isModerated')->will($this->returnValue($isModerated));
    $node->expects($this->any())->method('isCmPublished')->will($this->returnValue($isCmPublished));
    $node->expects($this->any())->method('isPublished')->will($this->returnValue($isPublished));
    $node->expects($this->any())->method('isArchived')->will($this->returnValue($isArchived));
    $node->expects($this->any())->method('isDraft')->will($this->returnValue($isDraft));
    $node->expects($this->any())->method('wasPublished')->will($this->returnValue($wasPublished));
    $node->expects($this->any())->method('didTransitionFromPublishedToArchived')->will($this->returnValue($wasPublished && $isArchived));
    $this->assertEquals($expected, $node->hasTriggeringChanges());
  }

  /**
   * Data provider for testHasTriggeringChanges.
   *
   * @return array
   *   An array of arrays, each containing a set of values for the parameters
   *   of testHasTriggeringChanges and the expected result.
   */
  public function hasTriggeringChangesDataProvider() {
    $combinations = [
      'isModerated' => [TRUE, FALSE],
      'isPublished' => [TRUE, FALSE],
      'moderationState' => [
        ContentModerationInterface::MODERATION_STATE_ARCHIVED,
        ContentModerationInterface::MODERATION_STATE_DRAFT,
        ContentModerationInterface::MODERATION_STATE_PUBLISHED,
      ],
      'wasPublished' => [TRUE, FALSE],
    ];
    $permutations = $this->generatePermutations($combinations);
    $result = [];
    foreach ($permutations as $permutation) {
      $isModerated = $permutation['isModerated'];
      $isPublished = $permutation['isPublished'];
      $isCmPublished = $permutation['moderationState'] === ContentModerationInterface::MODERATION_STATE_PUBLISHED;
      $isArchived = $permutation['moderationState'] === ContentModerationInterface::MODERATION_STATE_ARCHIVED;
      $isDraft = $permutation['moderationState'] === ContentModerationInterface::MODERATION_STATE_DRAFT;
      $wasPublished = $permutation['wasPublished'];

      /*
       * The following are contradictory states and should never occur.
       */
      switch (TRUE) {
        case $isPublished && $isArchived:
          // Archiving a node sets status to 0.
        case $isPublished && $isDraft:
          // Drafting a node sets status to 0.
          continue 2;

        default:
          break;
      }

      // By default, don't trigger content release.
      $expected = FALSE;

      /*
       * We consider the following to be triggering changes.
       */
      switch (TRUE) {
        case $isModerated && $isCmPublished:
        case $isModerated && $wasPublished && $isArchived:
        case !$isModerated && $isPublished:
        case !$isModerated && $wasPublished && !$isPublished:
          $expected = TRUE;
          break;
      }
      $name = sprintf(
        "%sisModerated, %sisCmPublished, %sisPublished, %sisArchived, %sisDraft, %swasPublished, %sexpected",
        $isModerated ? '' : '!',
        $isCmPublished ? '' : '!',
        $isPublished ? '' : '!',
        $isArchived ? '' : '!',
        $isDraft ? '' : '!',
        $wasPublished ? '' : '!',
        $expected ? '' : '!'
      );
      $result[$name] = [
        'isModerated' => $isModerated,
        'isCmPublished' => $isCmPublished,
        'isPublished' => $isPublished,
        'isArchived' => $isArchived,
        'isDraft' => $isDraft,
        'wasPublished' => $wasPublished,
        'expected' => $expected,
      ];
    }
    return $result;
  }

  /**
   * Test the shouldTriggerContentRelease() method.
   *
   * @param bool $hasTriggeringChanges
   *   Whether the node has triggering changes.
   * @param bool $alwaysTriggersContentRelease
   *   Whether the node always triggers content release.
   * @param bool $isFacility
   *   Whether the node is a facility.
   * @param bool $didChangeOperatingStatus
   *   Whether the node changed operating status.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::shouldTriggerContentRelease
   * @dataProvider shouldTriggerContentReleaseDataProvider
   */
  public function testShouldTriggerContentRelease(
    bool $hasTriggeringChanges,
    bool $alwaysTriggersContentRelease,
    bool $isFacility,
    bool $didChangeOperatingStatus,
    bool $expected
  ) {
    $node = $this->getMockForTrait(ContentReleaseTriggerTrait::class);

    if ($hasTriggeringChanges) {
      // Mock underlying methods so that `hasTriggeringChanges()` returns TRUE.
      // Publication of a CM-managed node is a triggering change.
      $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
      $node->expects($this->any())->method('isCmPublished')->will($this->returnValue(TRUE));
    }
    else {
      // Mock underlying methods so that `hasTriggeringChanges()` returns FALSE.
      // A new draft of a CM-managed node is not a triggering change.
      $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
      $node->expects($this->any())->method('isCmPublished')->will($this->returnValue(FALSE));
    }

    // Mock getType() so that `alwaysTriggersContentRelease()` returns the
    // expected value.
    // `banner` and `full_width_banner_alert` types always trigger content
    // release, while `page` do not (necessarily).
    $node->expects($this->any())->method('getType')->will($alwaysTriggersContentRelease ? $this->returnValue('banner') : $this->returnValue('page'));

    $node->expects($this->any())->method('isFacility')->will($this->returnValue($isFacility));
    $node->expects($this->any())->method('didChangeOperatingStatus')->will($this->returnValue($didChangeOperatingStatus));
    $this->assertEquals($expected, $node->shouldTriggerContentRelease());
  }

  /**
   * Data provider for testShouldTriggerContentRelease.
   *
   * @return array
   *   An array of arrays, each containing a set of values for the parameters
   *   of testShouldTriggerContentRelease and the expected result.
   */
  public function shouldTriggerContentReleaseDataProvider() {
    $combinations = [
      'hasTriggeringChanges' => [TRUE, FALSE],
      'alwaysTriggersContentRelease' => [TRUE, FALSE],
      'isFacility' => [TRUE, FALSE],
      'didChangeOperatingStatus' => [TRUE, FALSE],
    ];
    $permutations = $this->generatePermutations($combinations);
    $result = [];
    foreach ($permutations as $permutation) {
      $hasTriggeringChanges = $permutation['hasTriggeringChanges'];
      $alwaysTriggersContentRelease = $permutation['alwaysTriggersContentRelease'];
      $isFacility = $permutation['isFacility'];
      $didChangeOperatingStatus = $permutation['didChangeOperatingStatus'];

      /*
       * We consider the following to be triggering changes:
       * - anything with triggering changes that:
       *   - always triggers content release
       *     OR
       *   - both of the following:
       *     - is a facility
       *     - did change operating status
       */
      $expected = $hasTriggeringChanges && ($alwaysTriggersContentRelease || ($isFacility && $didChangeOperatingStatus));

      $name = sprintf(
        "%shasTriggeringChanges, %salwaysTriggersContentRelease, %sisFacility, %sdidChangeOperatingStatus, %sexpected",
        $hasTriggeringChanges ? '' : '!',
        $alwaysTriggersContentRelease ? '' : '!',
        $isFacility ? '' : '!',
        $didChangeOperatingStatus ? '' : '!',
        $expected ? '' : '!'
      );
      $result[$name] = [
        'hasTriggeringChanges' => $hasTriggeringChanges,
        'alwaysTriggersContentRelease' => $alwaysTriggersContentRelease,
        'isFacility' => $isFacility,
        'didChangeOperatingStatus' => $didChangeOperatingStatus,
        'expected' => $expected,
      ];
    }
    return $result;
  }

  /**
   * Verify that content types that trigger a release are detected correctly.
   *
   * @param string $type
   *   The content type.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::alwaysTriggersContentRelease
   * @dataProvider alwaysTriggersContentReleaseDataProvider
   */
  public function testAlwaysTriggersContentRelease(string $type, bool $expected) {
    $node = $this->getMockForTrait(ContentReleaseTriggerTrait::class);
    $node->expects($this->any())->method('getType')->will($this->returnValue($type));

    $this->assertEquals($expected, $node->alwaysTriggersContentRelease());
  }

  /**
   * Data provider for testAlwaysTriggersContentRelease.
   *
   * @return array
   *   An array of arrays, each containing a content type and the expected
   *   result of the alwaysTriggersContentRelease method.
   */
  public function alwaysTriggersContentReleaseDataProvider() {
    return [
      'banner' => [
        'type' => 'banner',
        'expected' => TRUE,
      ],
      'page' => [
        'type' => 'page',
        'expected' => FALSE,
      ],
      'health_care_local_facility' => [
        'type' => 'health_care_local_facility',
        'expected' => FALSE,
      ],
      'full_width_banner_alert' => [
        'type' => 'full_width_banner_alert',
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * Test isUnmoderatedAndWasPreviouslyPublished().
   *
   * @param bool $isModerated
   *   Whether the node is moderated.
   * @param bool $hasOriginal
   *   Whether the node has an original.
   * @param bool $isOriginalPublished
   *   Whether the original is published.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isUnmoderatedAndWasPreviouslyPublished
   * @dataProvider isUnmoderatedAndWasPreviouslyPublishedDataProvider
   */
  public function testIsUnmoderatedAndWasPreviouslyPublished(
    bool $isModerated,
    bool $hasOriginal,
    bool $isOriginalPublished,
    bool $expected
  ) {
    $original = $this->getMockBuilder(VaNodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $original->expects($this->any())
      ->method('isPublished')
      ->willReturn($isOriginalPublished);

    $node = $this->getMockForTrait(ContentReleaseTriggerTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue($isModerated));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue($hasOriginal));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));

    $this->assertEquals($expected, $node->isUnmoderatedAndWasPreviouslyPublished());
  }

  /**
   * Data provider for testIsUnmoderatedAndWasPreviouslyPublished.
   *
   * @return array
   *   An array of arrays, each containing a set of values for the parameters
   *   of testIsUnmoderatedAndWasPreviouslyPublished and the expected result.
   */
  public function isUnmoderatedAndWasPreviouslyPublishedDataProvider(): array {
    return [
      'unmoderated, has original, original published' => [
        'isModerated' => FALSE,
        'hasOriginal' => TRUE,
        'isOriginalPublished' => TRUE,
        'expected' => TRUE,
      ],
      'unmoderated, has original, original not published' => [
        'isModerated' => FALSE,
        'hasOriginal' => TRUE,
        'isOriginalPublished' => FALSE,
        'expected' => FALSE,
      ],
      'unmoderated, no original' => [
        'isModerated' => FALSE,
        'hasOriginal' => FALSE,
        'isOriginalPublished' => FALSE,
        'expected' => FALSE,
      ],
      'moderated, has original, original published' => [
        'isModerated' => TRUE,
        'hasOriginal' => TRUE,
        'isOriginalPublished' => TRUE,
        'expected' => FALSE,
      ],
      'moderated, has original, original not published' => [
        'isModerated' => TRUE,
        'hasOriginal' => TRUE,
        'isOriginalPublished' => FALSE,
        'expected' => FALSE,
      ],
      'moderated, no original' => [
        'isModerated' => TRUE,
        'hasOriginal' => FALSE,
        'isOriginalPublished' => FALSE,
        'expected' => FALSE,
      ],
    ];
  }

}
