<?php

namespace tests\phpunit\va_gov_form_builder\functional\Form;

use Drupal\node\Entity\Node;
use tests\phpunit\va_gov_form_builder\Traits\SharedConstants;
use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the NameAndDob form.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\NameAndDob
 */
class NameAndDobTest extends VaGovExistingSiteBase {

  use SharedConstants;
  use TestPageLoads;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * The Digital Form node.
   *
   * @var \Drupal\node\Entity\Node
   */
  private $node;

  /**
   * Returns the url for this form (for the given node)
   */
  private function getFormPageUrl() {
    return '/form-builder/' . $this->node->id() . '/name-and-dob';
  }

  /**
   * Reloads the node from the database.
   */
  private function reloadNode() {
    \Drupal::entityTypeManager()->getStorage('node')->resetCache([$this->node->id()]);
    $this->node = Node::load($this->node->id());
  }

  /**
   * Set up the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->loginFormBuilderUser();

    // Create a node that doesn't have any chapters.
    $this->node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $this->drupalGet($this->getFormPageUrl());
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    $this->sharedTestPageLoads($this->getFormPageUrl(), 'Collecting Name and Date of birth');
  }

  /**
   * Test that the page is not accessible to a user without privilege.
   */
  public function testPageDoesNotLoad() {
    $this->sharedTestPageDoesNotLoad($this->getFormPageUrl());
  }

  /**
   * Test that the form submission adds a chapter when not already present.
   */
  public function testFormSubmissionAddsChapter() {
    $formInput = [
      'step_name' => 'Your Personal Information',
    ];
    $this->submitForm($formInput, 'Continue');

    // Reload node and assert that chapters has been updated.
    $this->reloadNode();
    $this->assertCount(1, $this->node->get('field_chapters')->getValue());
  }

  /**
   * Test that the form submission does not add a chapter when already present.
   */
  public function testFormSubmissionDoesNotAddChapter() {
    // Add a chapter to begin.
    $nameAndDobParagraph = \Drupal::entityTypeManager()->getStorage('paragraph')->create([
      'type' => 'digital_form_name_and_date_of_bi',
      'field_title' => 'Your personal information',
      'field_include_date_of_birth' => TRUE,
    ]);
    $this->node->get('field_chapters')->appendItem($nameAndDobParagraph);
    $this->node->save();

    $formInput = [
      'step_name' => 'Your Personal Information',
    ];
    $this->submitForm($formInput, 'Continue');

    // Reload node and assert that chapters still has only one item.
    $this->reloadNode();
    $this->assertCount(1, $this->node->get('field_chapters')->getValue());
  }

  /**
   * Test that the 'Back' button takes the user to the form-info page.
   */
  public function testBackButton() {
    $this->click('.button#edit-back');
    $this->assertSession()->addressMatches('|form-builder/\d+/form-info|');
  }

}
