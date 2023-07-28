<?php

namespace tests\phpunit\va_gov_content_types\unit\Traits;

use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_types\Exception\NoOriginalExistsException;
use Drupal\va_gov_content_types\Exception\UnmoderatedContentTypeException;
use Drupal\va_gov_content_types\Traits\ContentModerationTransitionsTrait;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the ContentModerationTransitionsTrait trait.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_types\Traits\ContentModerationTransitionsTrait
 */
class ContentModerationTransitionsTraitTest extends VaGovUnitTestBase {

  /**
   * Test the getOriginalModerationState() function.
   *
   * @param string $originalModerationState
   *   The value of the moderation state field.
   *
   * @covers ::getOriginalModerationState
   * @dataProvider getOriginalModerationStateDataProvider
   */
  public function testGetOriginalModerationState(string $originalModerationState) : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $originalProphecy = $this->prophesize(VaNodeInterface::class);
    $originalProphecy->getModerationState()->willReturn($originalModerationState);
    $original = $originalProphecy->reveal();
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $this->assertEquals($originalModerationState, $node->getOriginalModerationState());
  }

  /**
   * Data provider for testIsModerated.
   *
   * @return array
   *   An array of arrays.
   */
  public function getOriginalModerationStateDataProvider() {
    return [
      'draft' => [
        'originalModerationState' => 'draft',
      ],
    ];
  }

  /**
   * Verify getOriginalModerationState() throws exception when no original.
   */
  public function testGetOriginalModerationStateNoOriginal() : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $this->expectException(NoOriginalExistsException::class);
    $node->getOriginalModerationState();
  }

  /**
   * Verify getOriginalModerationState() throws exception when not moderated.
   */
  public function testGetOriginalModerationStateNotModerated() : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(FALSE));
    $this->expectException(UnmoderatedContentTypeException::class);
    $node->getOriginalModerationState();
  }

  /**
   * Verify wasPublished() returns FALSE when no original.
   */
  public function testWasPublishedNoOriginal() {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(FALSE));
    $this->assertFalse($node->wasPublished());
  }

  /**
   * Verify wasArchived() returns FALSE when no original.
   */
  public function testWasArchivedNoOriginal() {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(FALSE));
    $this->assertFalse($node->wasArchived());
  }

  /**
   * Verify wasDraft() returns FALSE when no original.
   */
  public function testWasDraftNoOriginal() {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(FALSE));
    $this->assertFalse($node->wasDraft());
  }

  /**
   * Verify didTransitionFromPublishedToArchived() behavior.
   */
  public function testDidTransitionFromPublishedToArchivedNoOriginal() {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(FALSE));
    $this->assertFalse($node->didTransitionFromPublishedToArchived());
  }

  /**
   * Test the wasPublished() function.
   *
   * @param string $originalModerationState
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::wasPublished
   * @dataProvider wasPublishedDataProvider
   */
  public function testWasPublished(string $originalModerationState, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $originalProphecy = $this->prophesize(VaNodeInterface::class);
    $originalProphecy->getModerationState()->willReturn($originalModerationState);
    $original = $originalProphecy->reveal();
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $this->assertEquals($expected, $node->wasPublished());
  }

  /**
   * Data provider for testWasPublished.
   *
   * @return array
   *   An array of arrays.
   */
  public function wasPublishedDataProvider() {
    return [
      'draft' => [
        'originalModerationState' => 'draft',
        'expected' => FALSE,
      ],
      'published' => [
        'originalModerationState' => 'published',
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * Test the wasArchived() function.
   *
   * @param string $originalModerationState
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::wasArchived
   * @dataProvider wasArchivedDataProvider
   */
  public function testWasArchived(string $originalModerationState, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $originalProphecy = $this->prophesize(VaNodeInterface::class);
    $originalProphecy->getModerationState()->willReturn($originalModerationState);
    $original = $originalProphecy->reveal();
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $this->assertEquals($expected, $node->wasArchived());
  }

  /**
   * Data provider for testWasArchived.
   *
   * @return array
   *   An array of arrays.
   */
  public function wasArchivedDataProvider() {
    return [
      'draft' => [
        'originalModerationState' => 'draft',
        'expected' => FALSE,
      ],
      'archived' => [
        'originalModerationState' => 'archived',
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * Test the wasDraft() function.
   *
   * @param string $originalModerationState
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::wasDraft
   * @dataProvider wasDraftDataProvider
   */
  public function testWasDraft(string $originalModerationState, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $originalProphecy = $this->prophesize(VaNodeInterface::class);
    $originalProphecy->getModerationState()->willReturn($originalModerationState);
    $original = $originalProphecy->reveal();
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $this->assertEquals($expected, $node->wasDraft());
  }

  /**
   * Data provider for testWasDraft.
   *
   * @return array
   *   An array of arrays.
   */
  public function wasDraftDataProvider() {
    return [
      'draft' => [
        'originalModerationState' => 'draft',
        'expected' => TRUE,
      ],
      'archived' => [
        'originalModerationState' => 'archived',
        'expected' => FALSE,
      ],
    ];
  }

  /**
   * Test the isPublishedOrWasJustArchived() function.
   *
   * @param string $originalModerationState
   *   The value of the moderation state field.
   * @param string $moderationState
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::isPublishedOrWasJustArchived
   * @dataProvider isPublishedOrWasJustArchivedDataProvider
   */
  public function testIsPublishedOrWasJustArchived(string $originalModerationState, string $moderationState, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $originalProphecy = $this->prophesize(VaNodeInterface::class);
    $originalProphecy->getModerationState()->willReturn($originalModerationState);
    $original = $originalProphecy->reveal();
    $node->expects($this->any())->method('isCmPublished')->will($this->returnValue($moderationState === 'published'));
    $node->expects($this->any())->method('isArchived')->will($this->returnValue($moderationState === 'archived'));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $node->expects($this->any())->method('get')->will($this->returnValue((object) [
      'value' => $moderationState,
    ]));
    $this->assertEquals($expected, $node->isPublishedOrWasJustArchived());
  }

  /**
   * Data provider for testIsPublishedOrWasJustArchived.
   *
   * @return array
   *   An array of arrays.
   */
  public function isPublishedOrWasJustArchivedDataProvider() {
    return [
      'draft' => [
        'originalModerationState' => 'draft',
        'moderationState' => 'draft',
        'expected' => FALSE,
      ],
      'published' => [
        'originalModerationState' => 'published',
        'moderationState' => 'published',
        'expected' => TRUE,
      ],
      'archived' => [
        'originalModerationState' => 'archived',
        'moderationState' => 'archived',
        'expected' => FALSE,
      ],
      'published to archived' => [
        'originalModerationState' => 'published',
        'moderationState' => 'archived',
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * Test the didTransitionFromPublishedToArchived() function.
   *
   * @param string $originalModerationState
   *   The value of the moderation state field.
   * @param string $moderationState
   *   The value of the moderation state field.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::didTransitionFromPublishedToArchived
   * @dataProvider didTransitionFromPublishedToArchivedDataProvider
   */
  public function testDidTransitionFromPublishedToArchived(string $originalModerationState, string $moderationState, bool $expected) : void {
    $node = $this->getMockForTrait(ContentModerationTransitionsTrait::class);
    $node->expects($this->any())->method('isModerated')->will($this->returnValue(TRUE));
    $originalProphecy = $this->prophesize(VaNodeInterface::class);
    $originalProphecy->getModerationState()->willReturn($originalModerationState);
    $original = $originalProphecy->reveal();
    $node->expects($this->any())->method('isCmPublished')->will($this->returnValue($moderationState === 'published'));
    $node->expects($this->any())->method('isArchived')->will($this->returnValue($moderationState === 'archived'));
    $node->expects($this->any())->method('hasOriginal')->will($this->returnValue(TRUE));
    $node->expects($this->any())->method('getOriginal')->will($this->returnValue($original));
    $node->expects($this->any())->method('get')->will($this->returnValue((object) [
      'value' => $moderationState,
    ]));
    $this->assertEquals($expected, $node->didTransitionFromPublishedToArchived());
  }

  /**
   * Data provider for testDidTransitionFromPublishedToArchived.
   *
   * @return array
   *   An array of arrays.
   */
  public function didTransitionFromPublishedToArchivedDataProvider() {
    return [
      'draft' => [
        'originalModerationState' => 'draft',
        'moderationState' => 'draft',
        'expected' => FALSE,
      ],
      'published' => [
        'originalModerationState' => 'published',
        'moderationState' => 'published',
        'expected' => FALSE,
      ],
      'archived' => [
        'originalModerationState' => 'archived',
        'moderationState' => 'archived',
        'expected' => FALSE,
      ],
      'published to archived' => [
        'originalModerationState' => 'published',
        'moderationState' => 'archived',
        'expected' => TRUE,
      ],
    ];
  }

}
