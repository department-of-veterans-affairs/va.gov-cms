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
   *
   * @param string $oldValue
   *   The original value of the field.
   * @param string $newValue
   *   The new value of the field.
   * @param bool $expected
   *   The expected result of the didChangeField method.
   *
   * @covers ::didChangeField
   * @dataProvider didChangeFieldDataProvider
   */
  public function testDidChangeField(string $oldValue, string $newValue, bool $expected) {
    $node = $this->createNode([
      'bundle' => 'page',
    ]);
    $node->setNewRevision(TRUE);
    $node->setTitle($oldValue);
    $node->save();
    $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($node->getLoadedRevisionId());
    $node->setTitle($newValue);
    $node->save();
    $node->original = $revision;
    $this->assertEquals($expected, $node->didChangeField('title'));
  }

  /**
   * Data provider for testDidChangeField.
   *
   * @return array
   *   An array of arrays containing the parameters for the testDidChangeField
   *   test method.
   */
  public function didChangeFieldDataProvider() {
    return [
      [
        'Original Title',
        'Changed Title',
        TRUE,
      ],
      [
        'Original Title',
        'Original Title',
        FALSE,
      ],
    ];
  }

}
