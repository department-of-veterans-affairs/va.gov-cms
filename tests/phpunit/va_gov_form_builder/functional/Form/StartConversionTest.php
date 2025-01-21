<?php

namespace tests\phpunit\va_gov_form_builder\functional\Form;

use tests\phpunit\va_gov_form_builder\Traits\SharedConstants;
use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the StartConversion form.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\StartConversion
 */
class StartConversionTest extends VaGovExistingSiteBase {

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
    return '/form-builder/start-conversion';
  }

  /**
   * Set up the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->loginFormBuilderUser();
    $this->drupalGet($this->getFormPageUrl());
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    $this->sharedTestPageLoads($this->getFormPageUrl(), 'Start a new conversion');
  }

  /**
   * Test that the page is not accessible to a user without privilege.
   */
  public function testPageDoesNotLoad() {
    $this->sharedTestPageDoesNotLoad($this->getFormPageUrl());
  }

  /**
   * Test that the form submission succeeds.
   *
   * When proper information is entered, form should be submitted.
   */
  public function testFormSubmissionSucceeds() {
    // Fill in the form fields.
    $formInput = [
      'title' => 'Test Title',
      'field_va_form_number' => self::getUniqueVaFormNumber(),
      'field_omb_number' => '1111-1111',
      'field_respondent_burden' => '15',
      'field_expiration_date' => '2024-10-03',
    ];
    $this->submitForm($formInput, 'Continue');

    // Successful submission should take user to next page.
    $nextPageUrl = $this->getSession()->getCurrentUrl();
    $this->assertStringContainsString('/name-and-dob', $nextPageUrl);
  }

  /**
   * Test the form submission fails when missing required field.
   */
  public function testFormSubmissionFailsOnMissingRequiredField() {
    // Fill in the form fields.
    $formInput = [
      'title' => 'Test Title',
      'field_va_form_number' => self::getUniqueVaFormNumber(),
      'field_omb_number' => '1111-1111',
      'field_respondent_burden' => '15',
      // 'field_expiration_date' is required but missing
    ];
    $this->submitForm($formInput, 'Continue');

    // Check if the form submission was successful.
    $this->assertSession()->pageTextContains('Expiration date field is required.');
  }

  /**
   * Test the 'Back' button takes the user back to the Home page.
   */
  public function testBackButton() {
    $this->click('.button#edit-back');
    $this->assertSession()->addressEquals('/form-builder/home');
  }

}
