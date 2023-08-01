<?php

namespace tests\phpunit\va_gov_content_types\unit\Traits;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_types\Exception\NoOriginalExistsException;
use Drupal\va_gov_content_types\Traits\GetOriginalTrait;
use Prophecy\Argument;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the GetOriginalTrait trait.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_types\Traits\GetOriginalTrait
 */
class GetOriginalTraitTest extends VaGovUnitTestBase {

  /**
   * Test the hasOriginal() function.
   *
   * @param bool $hasOriginal
   *   Whether the node has an original.
   * @param bool $isRightClass
   *   Whether the original is the right class.
   * @param bool $expected
   *   The expected result.
   *
   * @covers ::hasOriginal
   * @dataProvider hasOriginalDataProvider
   */
  public function testHasOriginal(bool $hasOriginal, bool $isRightClass, bool $expected) : void {
    $node = $this->getMockForTrait(GetOriginalTrait::class);
    if ($hasOriginal) {
      $node->original = $isRightClass ? $this->createMock(VaNodeInterface::class) : $this->createMock(NodeInterface::class);
    }
    else {
      $node->original = NULL;
    }
    $this->assertEquals($expected, $node->hasOriginal());
  }

  /**
   * Data provider for testHasOriginal.
   *
   * @return array
   *   An array of arrays.
   */
  public function hasOriginalDataProvider() {
    return [
      'no original' => [
        FALSE,
        FALSE,
        FALSE,
      ],
      'wrong class' => [
        TRUE,
        FALSE,
        FALSE,
      ],
      'has original' => [
        TRUE,
        TRUE,
        TRUE,
      ],
    ];
  }

  /**
   * Test the getOriginal() function.
   *
   * @param bool $hasOriginal
   *   Whether the node has an original.
   *
   * @covers ::getOriginal
   * @dataProvider hasOriginalDataProvider
   */
  public function testGetOriginal(bool $hasOriginal) : void {
    $node = $this->getMockForTrait(GetOriginalTrait::class);
    if ($hasOriginal) {
      $node->original = $this->createMock(VaNodeInterface::class);
    }
    else {
      $node->original = NULL;
    }
    if (!$hasOriginal) {
      $this->expectException(NoOriginalExistsException::class);
    }
    $this->assertEquals($node->original, $node->getOriginal());
  }

  /**
   * Data provider for testGetOriginal.
   *
   * @return array
   *   An array of arrays.
   */
  public function getOriginalDataProvider() {
    return [
      'no original' => [
        FALSE,
      ],
      'has original' => [
        TRUE,
      ],
    ];
  }

  /**
   * Test the getOriginalField() function.
   */
  public function testGetOriginalField() {
    $node = $this->getMockForTrait(GetOriginalTrait::class);
    $node->original = $this->createMock(VaNodeInterface::class);
    $fieldItemListProphecy = $this->prophesize(FieldItemListInterface::class);
    $fieldItemListProphecy->getValue()->willReturn('test');
    $fieldItemList = $fieldItemListProphecy->reveal();
    $node->original->expects($this->once())->method('get')->with('field_test')->willReturn($fieldItemList);
    $this->assertEquals('test', $node->getOriginalField('field_test')->getValue());
  }

  /**
   * Test the didChangeField() function.
   */
  public function testDidChangeField() {
    $node = $this->getMockForTrait(GetOriginalTrait::class);
    $fieldItemListProphecy = $this->prophesize(FieldItemListInterface::class);
    $fieldItemListProphecy->getValue()->willReturn('test');
    $fieldItemListProphecy->equals(Argument::any())->willReturn(TRUE);
    $fieldItemList = $fieldItemListProphecy->reveal();
    $node->expects($this->once())->method('get')->with('field_test')->willReturn($fieldItemList);
    $node->original = $this->createMock(VaNodeInterface::class);
    $fieldItemListProphecy = $this->prophesize(FieldItemListInterface::class);
    $fieldItemListProphecy->getValue()->willReturn('test');
    $fieldItemList = $fieldItemListProphecy->reveal();
    $node->original->expects($this->once())->method('get')->with('field_test')->willReturn($fieldItemList);
    $this->assertFalse($node->didChangeField('field_test'));
  }

}
