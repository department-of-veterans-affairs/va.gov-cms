<?php

namespace tests\phpunit\va_gov_form_builder\functional\content_pages;

use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Home page.
 *
 * @group functional
 * @group all
 */
class HomeTest extends VaGovExistingSiteBase {
  use TestPageLoads;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * Returns the url for this page.
   */
  private function getPageUrl() {
    return '/form-builder/home';
  }

  /**
   * Set up the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->loginFormBuilderUser();
    $this->drupalGet($this->getPageUrl());
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    $this->sharedTestPageLoads($this->getPageUrl(), 'Start a new form, or select a previous form to work with');
  }

  /**
   * Test that the page is not accessible to a user without privilege.
   */
  public function testPageDoesNotLoad() {
    $this->sharedTestPageDoesNotLoad($this->getPageUrl());
  }

  /**
   * Test the 'Build a form' button.
   */
  public function testButton() {
    $this->click('a#form-builder-build-form-button');
    $this->assertSession()->addressEquals('/form-builder/start-conversion');
  }

}
