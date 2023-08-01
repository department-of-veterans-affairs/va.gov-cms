<?php

namespace tests\phpunit\va_gov_content_types\functional\Traits;

use Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the ContentModerationTrait trait.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Traits\ContentModerationTrait
 */
class ContentModerationTraitTest extends VaGovExistingSiteBase {

  /**
   * Verify that node moderation is detected correctly.
   *
   * @param string $type
   *   The content type.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isModerated
   * @dataProvider isModeratedDataProvider
   */
  public function testIsModerated(string $type, bool $expected) {
    $node = $this->getArbitraryNodeOfType($type);
    $this->assertEquals($expected, $node->isModerated());
  }

  /**
   * Data provider for testIsModerated.
   *
   * @return array
   *   An array of arrays, each containing a content type and the expected
   *   result of the isModerated method.
   */
  public function isModeratedDataProvider() {
    return [
      'page' => [
        'page',
        TRUE,
      ],
      'full_width_banner_alert' => [
        'full_width_banner_alert',
        FALSE,
      ],
    ];
  }

  /**
   * Ask an unmoderated node for its moderation_state.
   *
   * An exception should be thrown.
   *
   * @covers ::getModerationState
   */
  public function testGetModerationStateThrowsException() {
    $node = $this->getArbitraryNodeOfType('full_width_banner_alert');
    $this->expectException(UnmoderatedContentTypeException::class);
    $node->getModerationState();
  }

  /**
   * Test that the getModerationState method works.
   *
   * @covers ::getModerationState
   */
  public function testGetModerationState() {
    $node = $this->getArbitraryNodeOfType('page');
    $this->assertEquals($node->get('moderation_state')->value, $node->getModerationState());
  }

  /**
   * Get an arbitrary node with the given moderation state.
   *
   * The specifics of the node shouldn't matter, so we just grab the first one.
   *
   * Don't use this if you're going to change the node.
   *
   * @param string $moderationState
   *   The moderation state to get.
   *
   * @return \Drupal\va_gov_content_types\Entity\VaNodeInterface
   *   The node of the given type.
   */
  public function getArbitraryNodeWithModerationStatus(string $moderationState) {
    $entityTypeManager = \Drupal::entityTypeManager();
    $nodeStorage = $entityTypeManager->getStorage('node');
    $nids = $nodeStorage->getQuery()
      ->condition('moderation_state', $moderationState)
      ->accessCheck(FALSE)
      ->execute();
    $firstNid = reset($nids);
    $node = $nodeStorage->load($firstNid);
    return $node;
  }

  /**
   * Test that the isCmPublished method works.
   *
   * @covers ::isCmPublished
   */
  public function testIsCmPublished() {
    $node = $this->getArbitraryNodeWithModerationStatus('published');
    $this->assertTrue($node->isCmPublished());
  }

  /**
   * Test that the isArchived method works.
   *
   * @covers ::isArchived
   */
  public function testIsArchived() {
    $node = $this->getArbitraryNodeWithModerationStatus('archived');
    $this->assertTrue($node->isArchived());
  }

  /**
   * Test that the isDraft method works.
   *
   * @covers ::isDraft
   */
  public function testIsDraft() {
    $node = $this->getArbitraryNodeWithModerationStatus('draft');
    $this->assertTrue($node->isDraft());
  }

}
