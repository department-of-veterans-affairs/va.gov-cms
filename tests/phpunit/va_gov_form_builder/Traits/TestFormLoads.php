<?php

namespace tests\phpunit\va_gov_form_builder\Traits;

/**
 * Provides a trait for testing that forms load and do not load appropriately.
 */
trait TestFormLoads {

  /**
   * Logs-in a user with appropriate privileges.
   */
  private function loginFormBuilderUser() {
    $this->drupalLogin($this->createUser([
      'access form builder',
    ]));
  }

  /**
   * Test the form is accessible to a user with the correct privilege.
   *
   * @param string $url
   *   The (form) page to load.
   * @param string $expectedText
   *   The text expected to show on the loaded page.
   */
  private function sharedTestFormLoads($url, $expectedText) {
    // Log in a user with permission.
    $this->loginFormBuilderUser();

    // Navigate to page.
    $this->drupalGet($url);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($expectedText);
  }

  /**
   * Test the form is not accessible to a user without the correct privilege.
   *
   * @param string $url
   *   The (form) page to load.
   */
  private function sharedTestFormDoesNotLoad($url) {
    // Log in a user without permission.
    $this->drupalLogin($this->createUser([]));

    // Navigate to page.
    $this->drupalGet($url);

    $this->assertSession()->statusCodeNotEquals(200);
  }

}
