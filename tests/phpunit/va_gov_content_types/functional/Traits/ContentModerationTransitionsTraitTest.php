<?php

namespace tests\phpunit\va_gov_content_types\functional\Traits;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the ContentModerationTransitionsTrait trait.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Traits\ContentModerationTransitionsTrait
 */
class ContentModerationTransitionsTraitTest extends VaGovExistingSiteBase {

  /**
   * Test the getOriginalModerationState() method.
   *
   * @covers ::getOriginalModerationState
   */
  public function testGetOriginalModerationState() {
    $node = $this->createNode([
      'bundle' => 'page',
      'title' => '[TEST] Original Title',
      'moderation_state' => 'draft',
    ]);
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->setNewRevision(TRUE);
    $node->save();
    $node->set('moderation_state', 'published');
    $node->original = $revision;
    $original = $node->getOriginal();
    $this->assertEquals($node->id(), $original->id());
    $this->assertEquals($node->getOriginalModerationState(), 'draft');
  }

  /**
   * Test the isPublishedOrWasJustArchived() method.
   *
   * @param string $initialModerationState
   *   The initial moderation state.
   * @param string $newModerationState
   *   The new moderation state.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isPublishedOrWasJustArchived
   * @dataProvider isPublishedOrWasJustArchivedDataProvider
   */
  public function testIsPublishedOrWasJustArchived(string $initialModerationState, string $newModerationState, bool $expected) {
    $node = $this->createNode([
      'bundle' => 'page',
      'title' => '[TEST] Original Title',
      'moderation_state' => $initialModerationState,
    ]);
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->setNewRevision(TRUE);
    $node->save();
    $node->set('moderation_state', $newModerationState);
    $node->original = $revision;
    $original = $node->getOriginal();
    $this->assertEquals($node->id(), $original->id());
    $this->assertEquals($node->getOriginalModerationState(), $initialModerationState);
    $this->assertEquals($expected, $node->isPublishedOrWasJustArchived());
  }

  /**
   * Data provider for testIsPublishedOrWasJustArchived().
   *
   * @return array
   *   An array of arrays, each containing an initial moderation state, a new
   *   moderation state, and the expected result.
   */
  public function isPublishedOrWasJustArchivedDataProvider() {
    return [
      ['draft', 'published', TRUE],
      ['draft', 'archived', FALSE],
      ['published', 'archived', TRUE],
      ['published', 'published', TRUE],
      ['archived', 'archived', FALSE],
      ['archived', 'published', TRUE],
      ['archived', 'draft', FALSE],
    ];
  }

  /**
   * Test the didTransitionFromPublishedToArchived() method.
   *
   * @param string $initialModerationState
   *   The initial moderation state.
   * @param string $newModerationState
   *   The new moderation state.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::didTransitionFromPublishedToArchived
   * @dataProvider didTransitionFromPublishedToArchivedDataProvider
   */
  public function testDidTransitionFromPublishedToArchived(string $initialModerationState, string $newModerationState, bool $expected) {
    $node = $this->createNode([
      'bundle' => 'page',
      'title' => '[TEST] Original Title',
      'moderation_state' => $initialModerationState,
    ]);
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->setNewRevision(TRUE);
    $node->save();
    $node->set('moderation_state', $newModerationState);
    $node->original = $revision;
    $original = $node->getOriginal();
    $this->assertEquals($node->id(), $original->id());
    $this->assertEquals($node->getOriginalModerationState(), $initialModerationState);
    $this->assertEquals($expected, $node->didTransitionFromPublishedToArchived());
  }

  /**
   * Data provider for testDidTransitionFromPublishedToArchived().
   *
   * @return array
   *   An array of arrays, each containing an initial moderation state, a new
   *   moderation state, and the expected result.
   */
  public function didTransitionFromPublishedToArchivedDataProvider() {
    return [
      ['draft', 'published', FALSE],
      ['draft', 'archived', FALSE],
      ['published', 'archived', TRUE],
      ['published', 'published', FALSE],
      ['archived', 'archived', FALSE],
      ['archived', 'published', FALSE],
      ['archived', 'draft', FALSE],
    ];
  }

}
