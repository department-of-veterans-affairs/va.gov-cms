<?php

namespace tests\phpunit\va_gov_form_builder\unit\Form\Base;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderEditBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use tests\phpunit\va_gov_form_builder\Traits\AnonymousFormClass;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for the abstract class FormBuilderEditBase.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\Base\FormBuilderEditBase
 */
class FormBuilderEditBaseTest extends VaGovUnitTestBase {

  /**
   * An instance of an anonymous class that extends the abstract class.
   *
   * @var \Drupal\Core\Form\FormBuilderEditBase
   */
  private $classInstance;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Creates and sets a mock entityTypeManager.
   */
  private function setMockEntityTypeManager() {
    // Return null when nid = 0.
    // Return a Digital Form node when nid = 1.
    $nodeStorage = $this->createMock(EntityStorageInterface::class);
    $nodeStorage->method('load')
      ->willReturnMap([
        [0, NULL],
        [1, 'test123'],
      ]);

    $entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $entityTypeManager->method('getStorage')
      ->willReturn($nodeStorage);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Setup the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->setMockEntityTypeManager();

    // Create an anonymous instance of a class that extends our abstract class.
    $this->classInstance = new class($this->entityTypeManager) extends FormBuilderEditBase {
      use AnonymousFormClass;

      /**
       * getFields.
       */
      protected function getFields() {
        return [];
      }

      /**
       * setDigitalFormNodeFromFormState.
       */
      protected function setDigitalFormNodeFromFormState(array &$form, FormStateInterface $form_state) {
      }

    };
  }

  /**
   * Test that the buildForm method throws an error when node not found.
   */
  public function testBuildFormThrowsError() {
    $form = [];
    $formStateMock = $this->createMock(FormStateInterface::class);

    // Pass a bad node id so error is thrown.
    $nid = 0;
    $this->expectException(NotFoundHttpException::class);
    $form = $this->classInstance->buildForm($form, $formStateMock, $nid);
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

}
