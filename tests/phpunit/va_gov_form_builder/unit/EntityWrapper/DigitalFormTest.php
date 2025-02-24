<?php

namespace tests\phpunit\va_gov_form_builder\unit\EntityWrapper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
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
   * Creates a mock node of a given type.
   *
   * Defaults to `digital_form`.
   *
   * @param string $type
   *   The node type.
   *
   * @return \Drupal\node\NodeInterface
   *   The mock digital_form node.
   */
  private function createMockNode($type = 'digital_form') {
    $node = $this->createMock(NodeInterface::class);
    $node->method('getType')
      ->willReturn($type);

    return $node;
  }

  /**
   * Creates a mock paragraph of a given type (bundle).
   *
   * @param string $type
   *   The paragraph bundle type.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph
   *   The mock paragraph.
   */
  private function createMockParagraph($type) {
    $paragraph = $this->createMock(Paragraph::class);
    $paragraph->method('bundle')->willReturn($type);

    return $paragraph;
  }

  /**
   * Test the constructor with wrong node type passed in.
   *
   * Ensures that passing in a node that is not a
   * DigitalForm node thorws an error.
   */
  public function testConstructorWrongNodeType() {
    // Create a mock node that is some type
    // other than 'digital_form'.
    $node = $this->createMockNode('article');

    $this->expectException(\InvalidArgumentException::class);
    $this->digitalForm = new DigitalForm($this->entityTypeManager, $node);
  }

  /**
   * Test the __call magic method.
   */
  public function testCallMagicMethod() {
    $id = '123';
    $title = 'Test Digital Form 1';
    $field = $this->createMock(FieldItemListInterface::class);
    $field->value = 'Some field value';

    $node = $this->createMockNode();
    $node->method('getTitle')
      ->willReturn($title);
    $node->method('id')
      ->willReturn('123');
    $node->method('get')
      ->with('my_field')
      ->willReturn($field);

    $this->digitalForm = new DigitalForm($this->entityTypeManager, $node);

    // id()
    $idResult = $this->digitalForm->id();
    $this->assertEquals($id, $idResult);

    // getTitle()
    $getTitleResult = $this->digitalForm->getTitle();
    $this->assertEquals($title, $getTitleResult);

    // get()->value
    $getValueResult = $this->digitalForm->get('my_field')->value;
    $this->assertEquals($field->value, $getValueResult);

    // Unknown method.
    $this->expectException(\BadMethodCallException::class);
    $this->digitalForm->someUnknownMethod();
  }

  /**
   * Helper function to set up a test for hasChapterOfType.
   */
  private function setUpHasChapterOfTypeTest() {
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
    $mockNode = $this->createMock(NodeInterface::class);
    $mockNode->expects($this->once())
      ->method('hasField')
      ->with('field_chapters')
      ->willReturn(TRUE);
    $mockNode->expects($this->exactly(2))
      ->method('get')
      ->with('field_chapters')
      ->willReturn($mockField);
    $mockNode->expects($this->once())
      ->method('getType')
      ->willReturn('digital_form');

    // Instantiate an object to test.
    $this->digitalForm = new DigitalForm($this->entityTypeManager, $mockNode);
  }

  /**
   * Tests hasChapterOfType() with expected paragraph.
   */
  public function testHasChapterOfTypeWithExpectedParagraph() {
    $this->setUpHasChapterOfTypeTest();

    // Assert expected paragraph type returns true.
    $resultExpectedParagraphType = $this->digitalForm->hasChapterOfType('expected_paragraph');
    $this->assertTrue($resultExpectedParagraphType);
  }

  /**
   * Tests hasChapterOfType() with unexpected paragraph.
   */
  public function testHasChapterOfTypeWithUnexpectedParagraph() {
    $this->setUpHasChapterOfTypeTest();

    // Assert unexpected paragraph type returns false.
    $resultUnexpectedParagraphType = $this->digitalForm->hasChapterOfType('any_other_paragraph_type');
    $this->assertFalse($resultUnexpectedParagraphType);
  }

  /**
   * Helper function to set up a test for getStepStatus.
   */
  private function setUpGetStepStatusTest() {
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
    $this->setUpGetStepStatusTest();

    // Assert step status is incomplete for unknown name.
    $result = $this->digitalForm->getStepStatus('some_unknown_step_name');
    $this->assertEquals('incomplete', $result);
  }

  /**
   * Tests getStepStatus() with paragraph present.
   */
  public function testGetDigitalFormStepStatusParagraphPresent() {
    $this->setUpGetStepStatusTest();

    // Assert the status is 'complete' when paragraph is present.
    // Note: `contact_info` step = `digital_form_phone_and_email` paragraph.
    $result = $this->digitalForm->getStepStatus('contact_info');
    $this->assertEquals('complete', $result);
  }

  /**
   * Tests getStepStatus() with paragraph absent.
   */
  public function testGetDigitalFormStepStatusParagraphAbsent() {
    $this->setUpGetStepStatusTest();

    // Assert the status is 'incomplete' when paragraph is absent.
    // Note: `address_info` step = `digital_form_address` paragraph.
    $result = $this->digitalForm->getStepStatus('address_info');
    $this->assertEquals('incomplete', $result);
  }

  /**
   * Helper function to DRY up expectations for appendItem.
   *
   * @param string $paragraphType
   *   The type of paragraph expected to be passed to appendItem.
   */
  private function expectAppendedParagraph($paragraphType) {
    $mockChaptersField = $this->createMock(FieldItemListInterface::class);

    $mockChaptersField->expects($this->once())
      ->method('appendItem')
      ->with($this->callback(function ($paragraph) use ($paragraphType) {
        return method_exists($paragraph, 'bundle') && $paragraph->bundle() === $paragraphType;
      }));

    $this->digitalForm->expects($this->once())
      ->method('get')
      ->with('field_chapters')
      ->willReturn($mockChaptersField);
  }

  /**
   * Helper function to set up a test for addStep.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $mockParagraphEntityStorage
   *   The mock paragraph entity storage.
   */
  private function setUpAddStepTest($mockParagraphEntityStorage) {
    // Mock the entity type manager.
    $this->entityTypeManager
      ->method('getStorage')
      ->with('paragraph')
      ->willReturn($mockParagraphEntityStorage);

    $mockNode = $this->createMockNode();

    // Instantiate an object to test.
    $this->digitalForm = new DigitalForm($this->entityTypeManager, $mockNode);
  }

  /**
   * Helper function to set up a test for addStep with `your_personal_info`.
   *
   * @param mixed $expectedValues
   *   The expected values passed to the paragraph creation calls.
   */
  private function setUpAddStepYourPersonalInfoTest($expectedValues) {
    $nameAndDob = $this->createMockParagraph('digital_form_name_and_date_of_bi');
    $identificationInfo = $this->createMockParagraph('digital_form_identification_info');
    $yourPersonalInformation = $this->createMockParagraph('digital_form_your_personal_info');

    $mockParagraphEntityStorage = $this->createMock(EntityStorageInterface::class);
    $mockParagraphEntityStorage
      ->expects($this->exactly(3))
      ->method('create')
      ->withConsecutive(
        [
          $this->callback(fn($params) =>
            isset($params['type']) &&
            $params['type'] === 'digital_form_name_and_date_of_bi' &&
            isset($params['field_title']) &&
            $params['field_title'] === $expectedValues['field_name_and_date_of_birth']['field_title'] &&
            isset($params['field_include_date_of_birth']) &&
            $params['field_include_date_of_birth'] === $expectedValues['field_name_and_date_of_birth']['field_include_date_of_birth']
          ),
        ],
        [
          $this->callback(fn($params) =>
            isset($params['type']) &&
            $params['type'] === 'digital_form_identification_info' &&
            isset($params['field_title']) &&
            $params['field_title'] === $expectedValues['field_identification_information']['field_title'] &&
            isset($params['field_include_veteran_s_service']) &&
            $params['field_include_veteran_s_service'] === $expectedValues['field_identification_information']['field_include_veteran_s_service']
          ),
        ],
        [
          $this->callback(fn($params) =>
            isset($params['type']) && $params['type'] === 'digital_form_your_personal_info'
          ),
        ]
      )
      ->willReturnOnConsecutiveCalls(
          $nameAndDob,
          $identificationInfo,
          $yourPersonalInformation,
      );

    $this->setUpAddStepTest($mockParagraphEntityStorage);
    $this->expectAppendedParagraph('digital_form_your_personal_info');
  }

  /**
   * Tests addStep() with `your_personal_info` and default values.
   */
  public function testAddStepYourPersonalInfoWithDefaults() {
    // Expect the default values.
    $this->setUpAddStepYourPersonalInfoTest([
      'field_name_and_date_of_birth' => [
        'field_title' => 'Name and date of birth',
        'field_include_date_of_birth' => TRUE,
      ],
      'field_identification_information' => [
        'field_title' => 'Identification information',
        'field_include_veteran_s_service' => FALSE,
      ],
    ]);

    /* Call the method under test */
    $this->digitalForm->addStep('your_personal_info');
  }

  /**
   * Tests addStep() with `your_personal_info` and non-default values.
   */
  public function testAddStepYourPersonalInfo() {
    $nameAndDobTitle = 'My name-and-dob title';
    $includeDob = FALSE;
    $identificationInfoTitle = 'My identification-info title';
    $includeVeteranService = TRUE;

    $fields = [
      'field_name_and_date_of_birth' => [
        'field_title' => $nameAndDobTitle,
        'field_include_date_of_birth' => $includeDob,
      ],
      'field_identification_information' => [
        'field_title' => $identificationInfoTitle,
        'field_include_veteran_s_service' => $includeVeteranService,
      ],
    ];

    $this->setUpAddStepYourPersonalInfoTest($fields);

    /* Call the method under test */
    $this->digitalForm->addStep('your_personal_info', $fields);
  }

  /**
   * Helper function to set up a test for addStep with `address_info`.
   *
   * @param mixed $expectedValues
   *   The expected values passed to the paragraph creation calls.
   */
  private function setUpAddStepAddressInfoTest($expectedValues) {
    $addressInfo = $this->createMockParagraph('digital_form_address');

    $mockParagraphEntityStorage = $this->createMock(EntityStorageInterface::class);
    $mockParagraphEntityStorage
      ->expects($this->exactly(1))
      ->method('create')
      ->with(
        $this->callback(fn($params) =>
          isset($params['type']) &&
          $params['type'] === 'digital_form_address' &&
          isset($params['field_title']) &&
          $params['field_title'] === $expectedValues['field_title'] &&
          isset($params['field_military_address_checkbox']) &&
          $params['field_military_address_checkbox'] === $expectedValues['field_military_address_checkbox']
        ),
      )
      ->willReturn($addressInfo);

    $this->setUpAddStepTest($mockParagraphEntityStorage);
    $this->expectAppendedParagraph('digital_form_address');
  }

  /**
   * Tests addStep() with `address_info` and default values.
   */
  public function testAddStepAddressInfoWithDefaults() {
    // Expect the default values.
    $this->setUpAddStepAddressInfoTest([
      'field_title' => 'Mailing address',
      'field_military_address_checkbox' => TRUE,
    ]);

    /* Call the method under test */
    $this->digitalForm->addStep('address_info');
  }

  /**
   * Tests addStep() with `address_info` and non-default values.
   */
  public function testAddStepAddressInfo() {
    $addressInfoTitle = 'My address-info title';
    $includeMilitaryAddressCheckbox = FALSE;

    $fields = [
      'field_title' => $addressInfoTitle,
      'field_military_address_checkbox' => $includeMilitaryAddressCheckbox,
    ];

    $this->setUpAddStepAddressInfoTest($fields);

    /* Call the method under test */
    $this->digitalForm->addStep('address_info', $fields);
  }

  /**
   * Helper function to set up a test for addStep with `contact_info`.
   *
   * @param mixed $expectedValues
   *   The expected values passed to the paragraph creation calls.
   */
  private function setUpAddStepContactInfoTest($expectedValues) {
    $addressInfo = $this->createMockParagraph('digital_form_phone_and_email');

    $mockParagraphEntityStorage = $this->createMock(EntityStorageInterface::class);
    $mockParagraphEntityStorage
      ->expects($this->exactly(1))
      ->method('create')
      ->with(
        $this->callback(fn($params) =>
          isset($params['type']) &&
          $params['type'] === 'digital_form_phone_and_email' &&
          isset($params['field_title']) &&
          $params['field_title'] === $expectedValues['field_title'] &&
          isset($params['field_include_email']) &&
          $params['field_include_email'] === $expectedValues['field_include_email']
        ),
      )
      ->willReturn($addressInfo);

    $this->setUpAddStepTest($mockParagraphEntityStorage);
    $this->expectAppendedParagraph('digital_form_phone_and_email');
  }

  /**
   * Tests addStep() with `contact_info` and default values.
   */
  public function testAddStepContactInfoWithDefaults() {
    // Expect the default values.
    $this->setUpAddStepContactInfoTest([
      'field_title' => 'Phone and email address',
      'field_include_email' => TRUE,
    ]);

    /* Call the method under test */
    $this->digitalForm->addStep('contact_info');
  }

  /**
   * Tests addStep() with `contact_info` and non-default values.
   */
  public function testAddStepContactInfo() {
    $contactInfoTitle = 'My contact-info title';
    $includeEmail = FALSE;

    $fields = [
      'field_title' => $contactInfoTitle,
      'field_include_email' => $includeEmail,
    ];

    $this->setUpAddStepContactInfoTest($fields);

    /* Call the method under test */
    $this->digitalForm->addStep('contact_info', $fields);
  }

  /**
   * Tests addStep() with non-existent step name.
   */
  public function testAddStepNonExistentStep() {
    $node = $this->createMock(NodeInterface::class);
    $node->method('getType')
      ->willReturn('digital_form');

    $this->digitalForm = new DigitalForm($this->entityTypeManager, $node);
    $this->expectException(\InvalidArgumentException::class);
    $this->digitalForm->addStep('some_non_existent_step_name');
  }

}
