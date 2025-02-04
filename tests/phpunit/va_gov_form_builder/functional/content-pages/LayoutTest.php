<?php

namespace tests\phpunit\va_gov_form_builder\functional\content_pages;

use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Layout page.
 *
 * @group functional
 * @group all
 */
class LayoutTest extends VaGovExistingSiteBase {
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
  private function getFormPageUrl() {
    return "/form-builder/{$this->digitalFormNode->id()}/layout";
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

    // Add paragraphs.
    // Contact information.
    $contactInfoParagraph = \Drupal::entityTypeManager()->getStorage('paragraph')->create([
      'type' => 'digital_form_phone_and_email',
      'field_title' => $contactInfoTitle,
      'field_include_email' => TRUE,
    ]);
    $this->digitalFormNode->get('field_chapters')->appendItem($contactInfoParagraph);
    // List and loop.
    $listAndLoopParagraph = \Drupal::entityTypeManager()->getStorage('paragraph')->create([
      'type' => 'digital_form_list_loop',
      'field_title' => $listAndLoopTitle,
    ]);
    $this->digitalFormNode->get('field_chapters')->appendItem($listAndLoopParagraph);

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
    $this->sharedTestPageLoads($this->getFormPageUrl($this->digitalFormNode->id()), 'Build this form');
  }

  /**
   * Test that the page is not accessible to a user without privilege.
   */
  public function testPageDoesNotLoad() {
    $this->sharedTestPageDoesNotLoad($this->getFormPageUrl());
  }

  /**
   * Test that the page has the expected subtitle.
   *
   * The subtitle should be the form title.
   */
  public function testPageSubtitle() {
    $this->sharedTestPageHasExpectedSubtitle(
      $this->getFormPageUrl(),
      $this->digitalFormNode->getTitle(),
    );
  }

  /**
   * Test the "Form info" section.
   */
  public function testFormInfo() {
    $this->drupalGet($this->getFormPageUrl());
    $this->assertSession()->linkExists('View form info');

    $this->clickLink('View form info');
    $this->assertSession()->addressEquals("/form-builder/{$this->digitalFormNode->id()}/form-info");
  }

  /**
   * Test the "Introduction page" section.
   */
  public function testIntroductionPage() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View introduction page');
  }

  /**
   * Test the "Your personal information" section.
   */
  public function testYourPersonalInfo() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View personal information');
  }

  /**
   * Test the "Address information" section.
   */
  public function testAddressInfo() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View address information');
  }

  /**
   * Test the "Contact information" section.
   */
  public function testContactInfo() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View contact information');
  }

  /**
   * Test that additional (non-standard) steps are rendered.
   */
  public function testAdditionalSteps() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View your employers');

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('Add a step');
  }

  /**
   * Test the "Review and sign" section.
   */
  public function testReviewAndSign() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View review and sign page');
  }

  /**
   * Test the "Confirmation page" section.
   */
  public function testConfirmationPage() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View confirmation page');
  }

  /**
   * Test the "Viewing the form" section.
   */
  public function testViewingTheForm() {
    $this->drupalGet($this->getFormPageUrl());

    // There is no destination for this link yet.
    $this->assertSession()->linkExists('View form');
  }

}
