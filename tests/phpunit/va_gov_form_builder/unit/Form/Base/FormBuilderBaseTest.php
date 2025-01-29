<?php

namespace tests\phpunit\va_gov_form_builder\unit\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;
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

    // Create an anonymous instance of a class that extends our abstract class.
    $this->classInstance = new class($entityTypeManager) extends FormBuilderBase {
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
       * setDigitalFormNodeFromFormState.
       */
      protected function setDigitalFormNodeFromFormState(array &$form, FormStateInterface $form_state) {
        // Do nothing. We'll set the digitalFormNode via reflection.
      }

    };
  }

  /**
   * Test that the buildForm method throws error when node not passed.
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
    $isChangedFlag = $reflection->getProperty('digitalFormNodeIsChanged');
    $isChangedFlag->setAccessible(TRUE);

    $form = [];
    $formStateMock = $this->createMock(FormStateInterface::class);

    // Pass a good node id so no error is thrown.
    $nid = 1;
    $form = $this->classInstance->buildForm($form, $formStateMock, $nid);

    $isChangedFlagValue = $isChangedFlag->getValue($this->classInstance);
    $this->assertEquals($isChangedFlagValue, FALSE);
  }

  /**
   * Test the validateForm method with a Digital Form with no violations.
   */
  public function testValidateFormWithNoViolations() {
    $digitalFormNode = $this->createMock(Node::class);
    $violationList = new ConstraintViolationList([]);
    $digitalFormNode->method('validate')->willReturn($violationList);

    $reflection = new \ReflectionClass($this->classInstance);
    $digitalFormNodeProperty = $reflection->getProperty('digitalFormNode');
    $digitalFormNodeProperty->setAccessible(TRUE);
    $digitalFormNodeProperty->setValue($this->classInstance, $digitalFormNode);

    $form = [];

    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects($this->never())
      ->method('setErrorByName');

    $this->classInstance->validateForm($form, $formStateMock);
  }

  /**
   * Test the validateForm method with a node with applicable violations.
   */
  public function testValidateFormWithApplicableViolations() {
    $digitalFormNode = $this->createMock(Node::class);

    // Has violations on fields related to this form;
    // should raise errors.
    $violationList = new ConstraintViolationList([
      new ConstraintViolation('Invalid value 1', '', [], '', 'test_field_1', 'Invalid value'),
      new ConstraintViolation('Invalid value 2', '', [], '', 'test_field_2', 'Invalid value'),
    ]);

    $digitalFormNode->method('validate')->willReturn($violationList);

    $reflection = new \ReflectionClass($this->classInstance);
    $digitalFormNodeProperty = $reflection->getProperty('digitalFormNode');
    $digitalFormNodeProperty->setAccessible(TRUE);
    $digitalFormNodeProperty->setValue($this->classInstance, $digitalFormNode);

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
   * Test the validateForm method with a Digital Form with other violations.
   */
  public function testValidateFormWithOtherViolations() {
    $digitalFormNode = $this->createMock(Node::class);

    // Has violations, but not on fields related to this form;
    // should not raise errors.
    $violationList = new ConstraintViolationList([
      new ConstraintViolation('Invalid value 3', '', [], '', 'test_field_3', 'Invalid value'),
      new ConstraintViolation('Invalid value 4', '', [], '', 'test_field_4', 'Invalid value'),
    ]);

    $digitalFormNode->method('validate')->willReturn($violationList);

    $reflection = new \ReflectionClass($this->classInstance);
    $digitalFormNodeProperty = $reflection->getProperty('digitalFormNode');
    $digitalFormNodeProperty->setAccessible(TRUE);
    $digitalFormNodeProperty->setValue($this->classInstance, $digitalFormNode);

    $form = [];

    $formStateMock = $this->createMock(FormStateInterface::class);
    $formStateMock->expects($this->never())
      ->method('setErrorByName');

    $this->classInstance->validateForm($form, $formStateMock);
  }

  /**
   * Test the validateForm method with a deeply-nested violation path.
   */
  public function testValidateFormWithNestedViolationPath() {
    $digitalFormNode = $this->createMock(Node::class);

    // Has violation with a nested path; should raise an error the same way
    // as if the path were not nested (on `test_field_1`).
    $violationList = new ConstraintViolationList([
      new ConstraintViolation('Invalid value 1', '', [], '', 'test_field_1.0.value', 'Invalid value'),
    ]);

    $digitalFormNode->method('validate')->willReturn($violationList);

    $reflection = new \ReflectionClass($this->classInstance);
    $digitalFormNodeProperty = $reflection->getProperty('digitalFormNode');
    $digitalFormNodeProperty->setAccessible(TRUE);
    $digitalFormNodeProperty->setValue($this->classInstance, $digitalFormNode);

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
