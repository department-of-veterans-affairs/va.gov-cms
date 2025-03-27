<?php

namespace tests\phpunit\va_gov_form_builder\Traits;

/**
 * Provides a trait for testing that pages load and do not load appropriately.
 */
trait TestPageLoads {

  /**
   * Logs-in a user with appropriate privileges.
   */
  private function loginFormBuilderUser() {
    $this->drupalLogin($this->createUser([
      'access form builder',
    ]));
  }

  /**
   * Test the page is accessible to a user with the correct privilege.
   *
   * @param string $url
   *   The  page to load.
   * @param string $expectedText
   *   The text expected to show on the loaded page.
   * @param bool $login
   *   Whether a new user should be logged in.
   */
  private function sharedTestPageLoads($url, $expectedText, $login = TRUE) {
    if ($login) {
      // Log in a user with permission.
      $this->loginFormBuilderUser();
    }

    // Navigate to page.
    $this->drupalGet($url);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($expectedText);
  }

  /**
   * Test the page is not accessible to a user without the correct privilege.
   *
   * @param string $url
   *   The page to load.
   */
  private function sharedTestPageDoesNotLoad($url) {
    // Log in a user without permission.
    $this->drupalLogin($this->createUser([]));

    // Navigate to page.
    $this->drupalGet($url);

    $this->assertSession()->statusCodeNotEquals(200);
  }

  /**
   * Test the page has the expected subtitle.
   *
   * @param string $url
   *   The  page to load.
   * @param string $expectedSubtitle
   *   The expected subtitle.
   * @param bool $login
   *   Whether a new user should be logged in.
   */
  private function sharedTestPageHasExpectedSubtitle($url, $expectedSubtitle, $login = TRUE) {
    if ($login) {
      // Log in a user with permission.
      $this->loginFormBuilderUser();
    }

    // Navigate to page.
    $this->drupalGet($url);

    $page = $this->getSession()->getPage();
    $subtitleElement = $page->find('css', '.form-builder-subtitle');
    $this->assertEquals($subtitleElement->getText(), $expectedSubtitle);
  }

  /**
   * Test the page has the expected breadcrumbs.
   *
   * @param string $url
   *   The  page to load.
   * @param string[] $expectedBreadcrumbs
   *   The expected breadcrumbs.
   * @param bool $login
   *   Whether a new user should be logged in.
   */
  private function sharedTestPageHasExpectedBreadcrumbs($url, $expectedBreadcrumbs, $login = TRUE) {
    if ($login) {
      // Log in a user with permission.
      $this->loginFormBuilderUser();
    }

    // Navigate to page.
    $this->drupalGet($url);

    $page = $this->getSession()->getPage();
    $breadcrumbLinks = $page->findAll('css', '.form-builder-breadcrumbs__link');
    foreach ($breadcrumbLinks as $index => $breadcrumbLink) {
      $label = $breadcrumbLink->getText();
      $this->assertEquals($expectedBreadcrumbs[$index]['label'], $label);

      $url = $breadcrumbLink->getAttribute('href');
      $this->assertEquals($expectedBreadcrumbs[$index]['url'], $url);
    }
  }

}
