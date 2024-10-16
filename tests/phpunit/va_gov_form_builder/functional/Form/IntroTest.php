<?php

namespace tests\phpunit\va_gov_form_builder\functional\Form;

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

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * Setup the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->createUser(['edit any digital_form content']));
    $this->drupalGet('/form-builder/intro');
  }

  /**
   * Test the form is accessible to a user with the correct privilege.
   */
  public function testFormLoads() {
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Working with the Form Builder');
  }

  /**
   * Test the form is not accessible to a user without the correct privilege.
   */
  public function testFormDoesNotLoad() {
    // Log out the good user and log in a user without permission.
    $this->drupalLogin($this->createUser([]));
    $this->drupalGet('/form-builder/intro');

    $this->assertSession()->statusCodeNotEquals(200);
  }

  /**
   * Test the 'Start conversion' button.
   */
  public function testButton() {
    $this->click('.button#edit-start-conversion');
    $this->assertSession()->addressEquals('/form-builder/start-conversion');
  }

}
