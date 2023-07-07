<?php

namespace tests\phpunit\va_gov_content_types\functional\Traits;

use Drupal\va_gov_content_types\Exception\NoOriginalExistsException;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the GetOriginalTrait trait.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Traits\GetOriginalTrait
 */
class GetOriginalTraitTest extends VaGovExistingSiteBase {

  /**
   * Verify that an exception is thrown when a node has no original version.
   */
  public function testGetOriginalException() {
    $node = $this->createNode([
      'bundle' => 'page',
    ]);
    $this->expectException(NoOriginalExistsException::class);
    $node->getOriginal();
  }

  /**
   * Verify that a node with an original version returns the original version.
   */
  public function testGetOriginal() {
    $node = $this->createNode([
      'bundle' => 'page',
    ]);
    $node->setNewRevision(FALSE);
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->save();
    $node->original = $revision;
    $original = $node->getOriginal();
    $this->assertEquals($node->id(), $original->id());
  }

  /**
   * Test that the getOriginal method works for a node with a revision.
   */
  public function testGetOriginalRevision() {
    $node = $this->createNode([
      'bundle' => 'page',
    ]);
    $node->setNewRevision(TRUE);
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->save();
    $node->original = $revision;
    $original = $node->getOriginal();
    $this->assertEquals($node->id(), $original->id());
  }

  /**
   * Test that the getOriginalField method works.
   */
  public function testGetOriginalField() {
    $node = $this->createNode([
      'bundle' => 'page',
      'title' => '[TEST] Original Title',
    ]);
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->setNewRevision(TRUE);
    $node->save();
    $node->original = $revision;
    $original = $node->getOriginal();
    $this->assertEquals($node->id(), $original->id());
    $this->assertEquals($node->get('title')->value, $original->get('title')->value);
  }

  /**
   * Test that the getOriginalField method throws an exception appropriately.
   */
  public function testGetOriginalFieldException() {
    $node = $this->createNode([
      'bundle' => 'page',
    ]);
    $this->expectException(NoOriginalExistsException::class);
    $node->getOriginalField('title');
  }

  /**
   * Test that the didChangeField method works.
   */
  public function testDidChangeField() {
    $node = $this->createNode([
      'bundle' => 'page',
    ]);
    $node->setNewRevision(TRUE);
    $node->setTitle('Original Title');
    $node->save();
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->setTitle('Changed Title');
    $node->save();
    $node->original = $revision;
    $this->assertTrue($node->didChangeField('title'));
  }

}
