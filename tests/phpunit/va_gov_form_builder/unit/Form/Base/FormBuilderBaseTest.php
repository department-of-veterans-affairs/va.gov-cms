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

    // Title.
    $this->assertArrayHasKey('#title', $form);
    $this->assertEquals($form['#title'], 'Form Builder');

    // CSS.
    $this->assertArrayHasKey('#attached', $form);

    // 1. Form Builder Library.
    $this->assertArrayHasKey('html_head', $form['#attached']);
    $this->assertNotEmpty($form['#attached']['html_head']);

    $found = FALSE;
    foreach ($form['#attached']['html_head'] as $html_head_item) {
      if (
        isset($html_head_item[0]['#attributes']['href']) &&
        $html_head_item[0]['#attributes']['href'] === 'https://unpkg.com/@department-of-veterans-affairs/css-library@0.16.0/dist/tokens/css/variables.css'
      ) {
        $found = TRUE;
        break;
      }
    }
    $this->assertTrue($found, 'The html_head array contains a link with the unpkg token url.');

    // 2. External CSS.
    $this->assertArrayHasKey('library', $form['#attached']);
    $this->assertContains('va_gov_form_builder/va_gov_form_builder_styles', $form['#attached']['library']);
  }

}
