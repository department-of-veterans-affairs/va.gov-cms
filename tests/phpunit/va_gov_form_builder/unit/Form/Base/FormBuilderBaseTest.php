<?php

namespace tests\phpunit\va_gov_form_builder\unit\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
    $session = $this->createMock(SessionInterface::class);

    // Create an anonymous instance of a class that extends our abstract class.
    $this->classInstance = new class(
      $entityTypeManager,
      $this->digitalFormsService,
      $session,
    ) extends FormBuilderBase {
      use AnonymousFormClass;

      /**
       * buildForm.
       */
      public function buildForm(array $form, FormStateInterface $form_state) {
        // No need to do anything here. Just need to implement the method.
      }

    };
  }

  /**
   * Test that the constructor initializes `isCreate` to false.
   */
  public function testConstructorInitializesIsCreate() {
    $reflection = new \ReflectionClass($this->classInstance);
    $property = $reflection->getProperty('isCreate');
    $property->setAccessible(TRUE);
    $isCreateValue = $property->getValue($this->classInstance);

    $this->assertFalse($isCreateValue, 'The isCreate property should be initialized to false.');
  }

}
