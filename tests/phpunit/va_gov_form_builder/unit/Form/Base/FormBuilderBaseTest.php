<?php

namespace tests\phpunit\va_gov_form_builder\unit\Form\Base;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;
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

    // Create an anonymous instance of a class that extends our abstract class.
    $this->classInstance = new class() extends FormBuilderBase {
      use AnonymousFormClass;
    };
  }

  /**
   * Test the buildForm method.
   */
  public function testBuildForm() {
    $form = [];
    $formStateMock = $this->createMock(FormStateInterface::class);

    $form = $this->classInstance->buildForm($form, $formStateMock);

    $this->assertArrayHasKey('#title', $form);
    $this->assertEquals($form['#title'], 'Form Builder');
  }

}
