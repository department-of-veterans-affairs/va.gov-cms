<?php

namespace tests\phpunit\va_gov_form_builder\functional\Form;

use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use tests\phpunit\va_gov_form_builder\Traits\SharedConstants;
use tests\phpunit\va_gov_form_builder\Traits\TestPageLoads;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the StepStyle form.
 *
 * @group functional
 * @group all
 * @group disabled
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Form\StepStyle
 */
class StepStyleTest extends VaGovExistingSiteBase {

  use SharedConstants;
  use TestPageLoads;

  const STEP_LABEL_SESSION_KEY = 'form_builder:add_step:step_label';

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * The Digital Form object.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  protected $digitalForm;

  /**
   * The value of the step label.
   *
   * This value is populated on the previous
   * screen and the form on that screen
   * is submitted in the `setUp` method
   * in this test class so that this value
   * is set on the session variable as needed.
   *
   * @var string
   */
  protected $paragraphStepLabel;

  /**
   * Returns the url for this form.
   */
  private function getFormPageUrl() {
    return "/form-builder/{$this->digitalForm->id()}/step/add/step-style";
  }

  /**
   * Fetches the paragraph created by the form.
   *
   * If no paragraph was created, returns null.
   *
   * @return \Drupal\Core\Render\Element\Paragraph|null
   *   The fetched paragraph or null if not found.
   */
  private function fetchCreatedParagraph() {
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $query = $entityTypeManager
      ->getStorage('paragraph')
      ->getQuery();
    $query->condition('field_title', $this->paragraphStepLabel);
    $paragraphIds = $query
      ->accessCheck(FALSE)
      ->execute();

    if (empty($paragraphIds)) {
      return NULL;
    }

    $paragraphId = array_values($paragraphIds)[0];

    return $entityTypeManager
      ->getStorage('paragraph')
      ->load($paragraphId);
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

    // We need to simulate the step-label page's
    // form submission (the previous page in this
    // process) so the session variable gets
    // set appropriately. We cannot simply set the
    // session variable here, as this type of test
    // with `drupalGet` makes a simulated browser
    // request, so the session here is not the
    // same as the session in the application code.
    $this->paragraphStepLabel = 'My custom step label' . uniqid();
    $this->drupalGet("/form-builder/{$this->digitalForm->id()}/step/add/step-label");
    $stepLabelPage = $this->getSession()->getPage();
    $stepLabelPage->fillField('field_title', $this->paragraphStepLabel);
    $stepLabelPage->pressButton('Save and continue');

    $this->drupalGet($this->getFormPageUrl());
  }

  /**
   * Tear down after each test.
   */
  public function tearDown(): void {
    $paragraph = $this->fetchCreatedParagraph();
    if ($paragraph) {
      $paragraph->delete();
    }

    parent::tearDown();
  }

  /**
   * Test that the page is accessible to a user with the correct privilege.
   */
  public function testPageLoads() {
    $this->sharedTestPageLoads(
      $this->getFormPageUrl(),
      'Determine style of step to be added',
      // Do not re-login a user. We need the same user that
      // filled out the step-label form in the `setUp` method.
      FALSE
    );
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
      // Do not re-login a user. We need the same user that
      // filled out the step-label form in the `setUp` method.
      FALSE
    );
  }

  /**
   * Test that the page has the expected breadcrumbs.
   */
  public function testPageBreadcrumbs() {
    $this->sharedTestPageHasExpectedBreadcrumbs(
      $this->getFormPageUrl(),
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
          'label' => $this->paragraphStepLabel,
          'url' => "/form-builder/{$this->digitalForm->id()}/step/add/step-label",
        ],
        [
          'label' => 'Step style',
          'url' => "#content",
        ],
      ],
      // Do not re-login a user. We need the same user that
      // filled out the step-label form in the `setUp` method.
      FALSE
    );
  }

  /**
   * Test that the 'Edit step label' button works.
   */
  public function testEditStepLabelButton() {
    $this->submitForm([], 'Edit step label');

    // User should be taken to step-label page.
    $nextPageUrl = $this->getSession()->getCurrentUrl();
    $this->assertMatchesRegularExpression(
      '|/form-builder/\d+/step/add/step-label|',
      $nextPageUrl
    );
  }

  /**
   * Test that the form submission succeeds for single-question.
   */
  public function testFormSubmissionSucceedsSingleQuestion() {
    $this->submitForm([], 'Add a single question');

    // Successful submission should take user (for now)
    // to form-layout page.
    $nextPageUrl = $this->getSession()->getCurrentUrl();
    $this->assertMatchesRegularExpression(
      '|/form-builder/\d+|',
      $nextPageUrl
    );

    $createdParagraph = $this->fetchCreatedParagraph();
    $this->assertNotEmpty($createdParagraph);

    $createdParagraphType = $createdParagraph->bundle();
    $this->assertEquals($createdParagraphType, 'digital_form_custom_step');
  }

  /**
   * Test that the form submission succeeds for repeating-set.
   */
  public function testFormSubmissionSucceedsRepeatingSet() {
    $this->submitForm([], 'Add a repeating set');

    // Successful submission should take user (for now)
    // to form-layout page.
    $nextPageUrl = $this->getSession()->getCurrentUrl();
    $this->assertMatchesRegularExpression(
      '|/form-builder/\d+|',
      $nextPageUrl
    );

    $createdParagraph = $this->fetchCreatedParagraph();
    $this->assertNotEmpty($createdParagraph);

    $createdParagraphType = $createdParagraph->bundle();
    $this->assertEquals($createdParagraphType, 'digital_form_list_loop');
  }

}
