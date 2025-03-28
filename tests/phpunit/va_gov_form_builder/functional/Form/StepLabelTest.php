<?php

namespace tests\phpunit\va_gov_form_builder\functional\Form;

use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use tests\phpunit\va_gov_form_builder\Traits\ParagraphCreationTrait;
use tests\phpunit\va_gov_form_builder\Traits\SharedConstants;
use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the StepLabel form.
 *
 * @group functional
 * @group all
 * @group disabled
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\StepLabel
 */
class StepLabelTest extends VaGovExistingSiteBase {

  use ParagraphCreationTrait;
  use SharedConstants;
  use TestPageLoads;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * The Digital Form object.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  protected $digitalForm;

  /**
   * The paragraph object representing the step edited/created by this form.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph
   */
  protected $stepParagraph;

  /**
   * Returns the url for this form.
   *
   * @param string|null $mode
   *   'create' or 'edit'. Defaults to 'create'
   *   if not provided or not one of the two options.
   */
  private function getFormPageUrl($mode = NULL) {
    return match ($mode) {
      'edit' => "/form-builder/{$this->digitalForm->id()}/step/{$this->stepParagraph->id()}/step-label",
      default => "/form-builder/{$this->digitalForm->id()}/step/add/step-label",
    };
  }

  /**
   * Set up the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    // Create a new step paragraph.
    $this->stepParagraph = $this->createParagraph([
      'type' => 'digital_form_custom_step',
      'field_title' => 'Custom Step' . uniqid(),
    ]);
    $this->stepParagraph->save();

    // Create a new Digital Form node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'title' => 'Test Digital Form' . uniqid(),
      'field_chapters' => [$this->stepParagraph],
    ]);
    $node->save();
    $this->digitalForm = new DigitalForm($this->container->get('entity_type.manager'), $node);

    $this->loginFormBuilderUser();
    $this->drupalGet($this->getFormPageUrl());
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    $this->sharedTestPageLoads($this->getFormPageUrl(), 'Start building this step');
  }

  /**
   * Test that the page is not accessible to a user without privilege.
   */
  public function testPageDoesNotLoad() {
    $this->sharedTestPageDoesNotLoad($this->getFormPageUrl());
  }

  /**
   * Test that the page has the expected subtitle.
   */
  public function testPageSubtitle() {
    $this->sharedTestPageHasExpectedSubtitle(
      $this->getFormPageUrl(),
      $this->digitalForm->getTitle(),
    );
  }

  /**
   * Test that the page has the expected breadcrumbs in create mode.
   */
  public function testPageBreadcrumbsCreateMode() {
    $this->sharedTestPageHasExpectedBreadcrumbs(
      $this->getFormPageUrl(),
      [
        [
          'label' => 'Home',
          'url' => '/form-builder/home',
        ],
        [
          'label' => $this->digitalForm->getTitle(),
          'url' => "/form-builder/{$this->digitalForm->id()}",
        ],
        [
          'label' => 'Step label',
          'url' => "#content",
        ],
      ],
    );
  }

  /**
   * Test that the page has the expected breadcrumbs in edit mode.
   *
   * @todo Update and enable this test when edit-mode
   * breadcrumbs are updated to link to step-layout page.
   *
   * public function testPageBreadcrumbsEditMode() {
   * }
   */

  /**
   * Test that the page loads correctly in create mode.
   *
   * Ensures step label is empty (not pre-populated).
   */
  public function testPageLoadsCreateMode() {
    $page = $this->getSession()->getPage();

    // Ensure step-label (`field_title`) field is empty.
    $stepLabelInput = $page->findField('field_title');
    $this->assertEquals($stepLabelInput->getValue(), '');
  }

  /**
   * Test that the page loads correctly in edit mode.
   *
   * Ensures step label is populated as expected.
   */
  public function testPageLoadsInEditMode() {
    // Ensure page loads.
    $this->sharedTestPageLoads($this->getFormPageUrl('edit'), 'Start building this step');

    $page = $this->getSession()->getPage();

    // Ensure step-label (`field_title`) field is populated correctly.
    $stepLabelInput = $page->findField('field_title');
    $this->assertEquals(
      $stepLabelInput->getValue(),
      $this->stepParagraph->get('field_title')->value
    );
  }

  /**
   * Test that the form submission succeeds.
   *
   * When proper information is entered, session variable
   * should be set and user redirected to step-style page.
   */
  public function testFormSubmissionSucceeds() {
    // Fill in the form fields.
    $formInput = [
      'field_title' => 'My Custom Step Label' . uniqid(),
    ];
    $this->submitForm($formInput, 'Save and continue');

    // Successful submission should take user to step-style page.
    $nextPageUrl = $this->getSession()->getCurrentUrl();
    $this->assertMatchesRegularExpression(
      '|/form-builder/\d+/step/add/step-style|',
      $nextPageUrl
    );
  }

  /**
   * Test the form submission fails when missing required field.
   */
  public function testFormSubmissionFailsOnMissingRequiredField() {
    // Fill in the form fields.
    $formInput = [
      // Required but empty.
      'field_title' => '',
    ];
    $this->submitForm($formInput, 'Save and continue');

    // Check if the form submission was successful.
    $this->assertSession()->pageTextContains('Step label field is required.');
  }

}
