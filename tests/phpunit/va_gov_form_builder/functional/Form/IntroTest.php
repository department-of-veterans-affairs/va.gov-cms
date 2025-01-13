<?php

namespace tests\phpunit\va_gov_form_builder\functional\Form;

use tests\phpunit\va_gov_form_builder\Traits\TestFormLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Intro form.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\Intro
 */
class IntroTest extends VaGovExistingSiteBase {
  use TestFormLoads;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * Returns the url for this form (for the given node)
   */
  private function getFormPageUrl() {
    return '/form-builder/intro';
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
   * Test that the form is accessible to a user with the correct privilege.
   */
  public function testFormLoads() {
    $this->sharedTestFormLoads($this->getFormPageUrl(), 'Working with the Form Builder');
  }

  /**
   * Test that the form is not accessible to a user without privilege.
   */
  public function testFormDoesNotLoad() {
    $this->sharedTestFormDoesNotLoad($this->getFormPageUrl());
  }

  /**
   * Test the 'Start conversion' button.
   */
  public function testButton() {
    $this->click('.button#edit-start-conversion');
    $this->assertSession()->addressEquals('/form-builder/start-conversion');
  }

}
