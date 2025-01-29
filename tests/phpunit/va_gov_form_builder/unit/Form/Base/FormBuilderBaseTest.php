<?php

namespace tests\phpunit\va_gov_form_builder\unit\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use tests\phpunit\va_gov_form_builder\Traits\AnonymousFormClass;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for the abstract class FormBuilderBase.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\Base\FormBuilderBase
 */
class FormBuilderBaseTest extends VaGovUnitTestBase {

  /**
   * The Digital Forms service.
   *
   * @var \Drupal\va_gov_form_builder\Service\DigitalFormsService
   */
  protected $digitalFormsService;

  /**
   * An instance of an anonymous class that extends the abstract class.
   *
   * @var \Drupal\Core\Form\FormBuilderBase
   */
  private $classInstance;

  /**
   * Setup the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->digitalFormsService = $this->createMock(DigitalFormsService::class);

    // Create an anonymous instance of a class that extends our abstract class.
    $this->classInstance = new class($entityTypeManager, $this->digitalFormsService) extends FormBuilderBase {
      use AnonymousFormClass;

      /**
       * getFields.
       */
      protected function getFields() {
        return [
          'test_field_1',
          'test_field_2',
        ];
      }

      /**
       * setDigitalFormFromFormState.
       */
      protected function setDigitalFormFromFormState(array &$form, FormStateInterface $form_state) {
        // Do nothing. We'll set the digitalForm via reflection.
      }

    };
  }

  /**
   * Test that the buildForm method throws error when Digital Form not passed.
   */
  public function testBuildFormThrowsError() {
    $form = [];
    $formStateMock = $this->createMock(FormStateInterface::class);

    // Call `buildForm` without $node parameter.
    $this->expectException(\InvalidArgumentException::class);
    $form = $this->classInstance->buildForm($form, $formStateMock);
  }

  /**
   * Test that the buildForm method properly initializes the changed flag.
   */
  public function testBuildFormInitializesChangedFlag() {
    $reflection = new \ReflectionClass($this->classInstance);
    $isChangedFlag = $reflection->getProperty('digitalFormIsChanged');
    $isChangedFlag->setAccessible(TRUE);

    $form = [];
    $formStateMock = $this->createMock(FormStateInterface::class);

    // Pass a good Digital Form so no error is thrown.
    $digitalForm = $this->createMock(DigitalForm::class);
    $form = $this->classInstance->buildForm($form, $formStateMock, $digitalForm);

    $isChangedFlagValue = $isChangedFlag->getValue($this->classInstance);
    $this->assertEquals($isChangedFlagValue, FALSE);
  }

  /**
   * Helper function to set up tests for violations.
   *
   * @param \Symfony\Component\Validator\ConstraintViolationList $violationList
   *   The expected violations.
   */
  private function setUpViolationTest($violationList = NULL) {
    if (!$violationList) {
      $violationList = new ConstraintViolationList([]);
    }

    $digitalForm = $this->getMockBuilder(DigitalForm::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['__call'])
      ->getMock();

    $digitalForm->method('__call')
      ->willReturnCallback(function ($name, $arguments) use ($violationList) {
        if ($name === 'validate') {
          return $violationList;
        }
        return NULL;
      });

    $reflection = new \ReflectionClass($this->classInstance);
    $digitalFormProperty = $reflection->getProperty('digitalForm');
    $digitalFormProperty->setAccessible(TRUE);
    $digitalFormProperty->setValue($this->classInstance, $digitalForm);
  }

  /**
   * Test the validateForm method with a Digital Form with no violations.
   */
  public function testValidateFormWithNoViolations() {
    $this->setUpViolationTest();

    $form = [];

    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects($this->never())
      ->method('setErrorByName');

    $this->classInstance->validateForm($form, $formStateMock);
  }

  /**
   * Test validateForm method with a Digital Form with applicable violations.
   */
  public function testValidateFormWithApplicableViolations() {
    // Has violations on fields related to this form;
    // should raise errors.
    $violationList = new ConstraintViolationList([
      new ConstraintViolation('Invalid value 1', '', [], '', 'test_field_1', 'Invalid value'),
      new ConstraintViolation('Invalid value 2', '', [], '', 'test_field_2', 'Invalid value'),
    ]);

    $this->setUpViolationTest($violationList);

    $form = [];

    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects($this->exactly(2))
      ->method('setErrorByName')
      ->withConsecutive(
        ['test_field_1', 'Invalid value 1'],
        ['test_field_2', 'Invalid value 2'],
      );

    $this->classInstance->validateForm($form, $formStateMock);
  }

  /**
   * Test validateForm method with a Digital Form with other violations.
   */
  public function testValidateFormWithOtherViolations() {
    // Has violations, but not on fields related to this form;
    // should not raise errors.
    $violationList = new ConstraintViolationList([
      new ConstraintViolation('Invalid value 3', '', [], '', 'test_field_3', 'Invalid value'),
      new ConstraintViolation('Invalid value 4', '', [], '', 'test_field_4', 'Invalid value'),
    ]);

    $this->setUpViolationTest($violationList);

    $form = [];

    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects($this->never())
      ->method('setErrorByName');

    $this->classInstance->validateForm($form, $formStateMock);
  }

  /**
   * Test validateForm method with a deeply-nested violation path.
   */
  public function testValidateFormWithNestedViolationPath() {
    // Has violation with a nested path; should raise an error the same way
    // as if the path were not nested (on `test_field_1`).
    $violationList = new ConstraintViolationList([
      new ConstraintViolation('Invalid value 1', '', [], '', 'test_field_1.0.value', 'Invalid value'),
    ]);

    $this->setUpViolationTest($violationList);

    $form = [];

    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects($this->exactly(1))
      ->method('setErrorByName')
      ->withConsecutive(
        ['test_field_1', 'Invalid value 1'],
      );

    $this->classInstance->validateForm($form, $formStateMock);
  }

}
