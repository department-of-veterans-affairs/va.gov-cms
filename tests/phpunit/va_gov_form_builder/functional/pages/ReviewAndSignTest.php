<?php

namespace Tests\va_gov_form_builder\functional\pages;

use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Review-and-sign page.
 *
 * @group functional
 * @group all
 */
class ReviewAndSignTest extends VaGovExistingSiteBase {
  use TestPageLoads;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * The Digital Form node created for these tests.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $digitalFormNode;

  /**
   * Returns the url for this page.
   */
  private function getPageUrl() {
    return "/form-builder/{$this->digitalFormNode->id()}/review-and-sign";
  }

  /**
   * Helper method to generate a test node.
   */
  private function generateTestNode(
    $title = NULL,
    $formNumber = '99-9999',
    $contactInfoTitle = 'Contact information',
    $listAndLoopTitle = 'Your employers',
  ) {
    if (!$title) {
      $title = 'Test Digital Form ' . uniqid();
    }

    // Create a new Digital Form node.
    $this->digitalFormNode = $this->createNode([
      'type' => 'digital_form',
      'title' => $title,
      'field_chapters' => [],
      'field_va_form_number' => $formNumber,
    ]);

    // Save node.
    $this->digitalFormNode->save();
  }

  /**
   * Set up the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->generateTestNode();
    $this->loginFormBuilderUser();
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    // Ensure page loads.
    $this->sharedTestPageLoads($this->getPageUrl(), 'Reviewing the submission');
  }

  /**
   * Test that the page is not accessible to a user without privilege.
   */
  public function testPageDoesNotLoad() {
    $this->sharedTestPageDoesNotLoad($this->getPageUrl());
  }

  /**
   * Test that the page has the expected subtitle.
   *
   * The subtitle should be the form title.
   */
  public function testPageSubtitle() {
    $this->sharedTestPageHasExpectedSubtitle(
      $this->getPageUrl(),
      $this->digitalFormNode->getTitle(),
    );
  }

  /**
   * Test that the page has the expected breadcrumbs.
   */
  public function testPageBreadcrumbs() {
    $this->sharedTestPageHasExpectedBreadcrumbs(
      $this->getPageUrl(),
      [
        [
          'label' => 'Home',
          'url' => '/form-builder/home',
        ],
        [
          'label' => $this->digitalFormNode->getTitle(),
          'url' => "/form-builder/{$this->digitalFormNode->id()}",
        ],
        [
          'label' => 'Review page',
          'url' => "#content",
        ],
      ],
    );
  }

  /**
   * Test the primary button.
   */
  public function testPrimaryButton() {
    $this->drupalGet($this->getPageUrl());
    $this->click('a#form-builder-primary-button');
    $this->assertSession()->addressEquals("/form-builder/{$this->digitalFormNode->id()}");
  }

}
