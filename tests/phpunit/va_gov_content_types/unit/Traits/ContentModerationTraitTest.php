<?php

namespace tests\phpunit\va_gov_content_types\unit\Traits;

use Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException;
use Drupal\va_gov_content_types\Traits\ContentModerationTrait;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the ContentModerationTrait trait.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_types\Traits\ContentModerationTrait
 */
class ContentModerationTraitTest extends VaGovUnitTestBase {

  /**
   * Test the isModerated() function.
   *
   * @param bool $hasField
   *   Whether the node has a moderation state field.
   * @param string|null $moderationStateValue
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isModerated
   * @dataProvider isModeratedDataProvider
   */
  public function testIsModerated(bool $hasField, string|null $moderationStateValue, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTrait::class);
    $node->expects($this->any())->method('hasField')->will($this->returnValue($hasField));
    if ($hasField) {
      $node->expects($this->any())->method('get')->will($this->returnValue($moderationStateValue));
    }

  }

  /**
   * Data provider for testIsModerated.
   *
   * @return array
   *   An array of arrays.
   */
  public function isModeratedDataProvider() {
    return [
      'no field' => [
        FALSE,
        NULL,
        FALSE,
      ],
      'no value' => [
        TRUE,
        NULL,
        FALSE,
      ],
      'draft' => [
        TRUE,
        'draft',
        TRUE,
      ],
      'published' => [
        TRUE,
        'published',
        TRUE,
      ],
      'archived' => [
        TRUE,
        'archived',
        TRUE,
      ],
    ];
  }

  /**
   * Verify the getModerationState() function throws an exception.
   */
  public function testGetModerationStateException() : void {
    $node = $this->getMockForTrait(ContentModerationTrait::class);
    $node->expects($this->any())->method('hasField')->will($this->returnValue(FALSE));
    $this->expectException(UnmoderatedContentTypeException::class);
    $node->getModerationState();
  }

  /**
   * Test the getModerationState() function.
   *
   * @param string|null $moderationStateValue
   *   The value of the moderation state field.
   *
   * @covers ::getModerationState
   * @dataProvider getModerationStateDataProvider
   */
  public function testGetModerationState(string $moderationStateValue) : void {
    $node = $this->getMockForTrait(ContentModerationTrait::class);
    $node->expects($this->any())->method('hasField')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('get')->will($this->returnValue((object) [
      'value' => $moderationStateValue,
    ]));
    $this->assertEquals($moderationStateValue, $node->getModerationState());
  }

  /**
   * Data provider for testIsModerated.
   *
   * @return array
   *   An array of arrays.
   */
  public function getModerationStateDataProvider() {
    return [
      'draft' => [
        'draft',
      ],
      'published' => [
        'published',
      ],
      'archived' => [
        'archived',
      ],
    ];
  }

  /**
   * Test the isCmPublished() function.
   *
   * @param string $moderationStateValue
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isCmPublished
   * @dataProvider isCmPublishedDataProvider
   */
  public function testIsCmPublished(string $moderationStateValue, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTrait::class);
    $node->expects($this->any())->method('hasField')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('get')->will($this->returnValue((object) [
      'value' => $moderationStateValue,
    ]));
    $this->assertEquals($expected, $node->isCmPublished());
  }

  /**
   * Data provider for testIsCmPublished.
   *
   * @return array
   *   An array of arrays.
   */
  public function isCmPublishedDataProvider() {
    return [
      'draft' => [
        'draft',
        FALSE,
      ],
      'published' => [
        'published',
        TRUE,
      ],
      'archived' => [
        'archived',
        FALSE,
      ],
    ];
  }

  /**
   * Test the isArchived() function.
   *
   * @param string $moderationStateValue
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isArchived
   * @dataProvider isArchivedDataProvider
   */
  public function testIsArchived(string $moderationStateValue, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTrait::class);
    $node->expects($this->any())->method('hasField')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('get')->will($this->returnValue((object) [
      'value' => $moderationStateValue,
    ]));
    $this->assertEquals($expected, $node->isArchived());
  }

  /**
   * Data provider for testIsArchived.
   *
   * @return array
   *   An array of arrays.
   */
  public function isArchivedDataProvider() {
    return [
      'draft' => [
        'draft',
        FALSE,
      ],
      'published' => [
        'published',
        FALSE,
      ],
      'archived' => [
        'archived',
        TRUE,
      ],
    ];
  }

  /**
   * Test the isDraft() function.
   *
   * @param string $moderationStateValue
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isDraft
   * @dataProvider isDraftDataProvider
   */
  public function testIsDraft(string $moderationStateValue, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTrait::class);
    $node->expects($this->any())->method('hasField')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('get')->will($this->returnValue((object) [
      'value' => $moderationStateValue,
    ]));
    $this->assertEquals($expected, $node->isDraft());
  }

  /**
   * Data provider for testIsDraft.
   *
   * @return array
   *   An array of arrays.
   */
  public function isDraftDataProvider() {
    return [
      'draft' => [
        'draft',
        TRUE,
      ],
      'published' => [
        'published',
        FALSE,
      ],
      'archived' => [
        'archived',
        FALSE,
      ],
    ];
  }

}
