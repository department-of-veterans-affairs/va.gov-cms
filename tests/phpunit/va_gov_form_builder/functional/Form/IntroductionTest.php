<?php

namespace Tests\va_gov_form_builder\functional\Form;

use tests\phpunit\va_gov_form_builder\Traits\SharedConstants;
use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;

/**
 * Functional test of the IntroPage form.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\FormInfo
 */
class IntroductionTest extends VaGovExistingSiteBase {

  use SharedConstants;
  use TestPageLoads;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * Returns the url for this form.
   */
  private function getFormPageUrl() {
    return "/form-builder/{$this->digitalForm->id()}/intro";
  }

  /**
   * Set up the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    // Create a new Digital Form node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'title' => 'Test Digital Form' . uniqid(),
      'field_chapters' => [],
    ]);
    $this->digitalForm = new DigitalForm($this->container->get('entity_type.manager'), $node);

    $this->loginFormBuilderUser();
    $this->drupalGet($this->getFormPageUrl());
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    $this->sharedTestPageLoads($this->getFormPageUrl(), 'Provide introduction page content');
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
      FALSE);
  }

  /**
   * Test that the page has the expected breadcrumbs in edit mode.
   */
  public function testPageBreadcrumbs() {
    $this->sharedTestPageHasExpectedBreadcrumbs(
      $this->getFormPageUrl($this->digitalForm->id()),
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
          'label' => 'Introduction page',
          'url' => "#content",
        ],
      ],
    );
  }

  /**
   * Test that the form submission succeeds.
   *
   * When proper information is entered, form should be submitted.
   */
  public function testFormSubmissionSucceeds() {
    // Fill in the form fields.
    $formInput = [
      'intro_text' => 'Filling out this test content with a long string',
      'what_to_know[0]' => 'Bullet one',
    ];
    $this->submitForm($formInput, 'Save and continue');

    // Successful submission should take user to form's layout page.
    $nextPageUrl = $this->getSession()->getCurrentUrl();
    $this->assertMatchesRegularExpression('|/form-builder/\d+|', $nextPageUrl);
  }

  /**
   * Test the form submission fails when missing required field.
   */
  public function testFormSubmissionFailsOnMissingRequiredField() {
    // Fill in the form fields.
    $formInput = [
      // 'intro_text' is required but missing
      'what_to_know[0]' => 'Bullet one',
    ];
    $this->submitForm($formInput, 'Save and continue');

    // Check if the form submission was unsuccessful.
    $this->assertSession()->pageTextContains('Intro paragraph field is required.');
  }

}
