<?php

namespace tests\phpunit\va_gov_form_builder\unit\EntityWrapper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for the DigitalForm class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
 */
class DigitalFormTest extends VaGovUnitTestBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The DigitalForm object under test.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  private $digitalForm;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a mock EntityTypeManagerInterface.
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
  }

  /**
   * Helper function to set up a mock query for paragraphs.
   */
  private function setUpMockQueryParagraph() {
    // Mock the paragraph.
    $mockParagraph = $this->createMock(Paragraph::class);
    $mockParagraph->expects($this->once())
      ->method('bundle')
      ->willReturn('expected_paragraph');

    // Mock the entity storage.
    $entityStorage = $this->createMock(EntityStorageInterface::class);
    $entityStorage->expects($this->once())
      ->method('load')
      ->willReturnMap([
        ['1', $mockParagraph],
      ]);

    // Mock the entity type manager.
    $this->entityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('paragraph')
      ->willReturn($entityStorage);
  }

  /**
   * Helper function to create and return a mock a node with a paragraph.
   */
  private function createMockNodeWithParagraph() {
    // Mock the field_chapters field.
    $mockField = $this->createMock(FieldItemListInterface::class);
    $mockField->expects($this->once())
      ->method('isEmpty')
      ->willReturn(FALSE);
    $mockField->expects($this->once())
      ->method('getValue')
      ->willReturn([
        ['target_id' => '1'],
      ]);

    // Mock the node.
    $mockNode = $this->createMock(Node::class);
    $mockNode->expects($this->once())
      ->method('hasField')
      ->with('field_chapters')
      ->willReturn(TRUE);
    $mockNode->expects($this->exactly(2))
      ->method('get')
      ->with('field_chapters')
      ->willReturn($mockField);

    return $mockNode;
  }

  /**
   * Helper function to set up tests for hasChapterOfType.
   */
  private function setUpHasChapterOfType() {
    $this->setUpMockQueryParagraph();
    $node = $this->createMockNodeWithParagraph();

    // Instantiate an object to test.
    $this->digitalForm = new DigitalForm($this->entityTypeManager, $node);
  }

  /**
   * Tests hasChapterOfType() with expected paragraph.
   */
  public function testHasChapterOfTypeWithExpectedParagraph() {
    $this->setUpHasChapterOfType();

    // Assert expected paragraph type returns true.
    $resultExpectedParagraphType = $this->digitalForm->hasChapterOfType('expected_paragraph');
    $this->assertTrue($resultExpectedParagraphType);
  }

  /**
   * Tests hasChapterOfType() with unexpected paragraph.
   */
  public function testHasChapterOfTypeWithUnexpectedParagraph() {
    $this->setUpHasChapterOfType();

    // Assert unexpected paragraph type returns false.
    $resultUnexpectedParagraphType = $this->digitalForm->hasChapterOfType('any_other_paragraph_type');
    $this->assertFalse($resultUnexpectedParagraphType);
  }

  /**
   * Helper function to set up tests for getStepStatus.
   */
  private function setUpGetStepStatus() {
    $this->digitalForm = $this->getMockBuilder(DigitalForm::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['hasChapterOfType'])
      ->getMock();

    // Mock the behavior of hasChapterOfType method.
    $this->digitalForm->method('hasChapterOfType')
      ->willReturnMap([
        ['digital_form_phone_and_email', TRUE],
        ['digital_form_address', FALSE],
      ]);

  }

  /**
   * Tests getStepStatus() with unknown step name.
   */
  public function testGetDigitalFormStepStatusUnknownStepName() {
    $this->setUpGetStepStatus();

    // Assert step status is incomplete for unknown name.
    $result = $this->digitalForm->getStepStatus('some_unknown_step_name');
    $this->assertEquals('incomplete', $result);
  }

  /**
   * Tests getStepStatus() with paragraph present.
   */
  public function testGetDigitalFormStepStatusParagraphPresent() {
    $this->setUpGetStepStatus();

    // Assert the status is 'complete' when paragraph is present.
    // Note: `contact_info` step = `digital_form_phone_and_email` paragraph.
    $result = $this->digitalForm->getStepStatus('contact_info');
    $this->assertEquals('complete', $result);
  }

  /**
   * Tests getStepStatus() with paragraph absent.
   */
  public function testGetDigitalFormStepStatusParagraphAbsent() {
    $this->setUpGetStepStatus();

    // Assert the status is 'incomplete' when paragraph is absent.
    // Note: `address_info` step = `digital_form_address` paragraph.
    $result = $this->digitalForm->getStepStatus('address_info');
    $this->assertEquals('incomplete', $result);
  }

}
