<?php

namespace tests\phpunit\va_gov_form_builder\functional\Controller;

use Drupal\Core\Url;
use Drupal\va_gov_form_builder\Controller\VaGovFormBuilderController;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use tests\phpunit\va_gov_form_builder\Traits\ParagraphCreationTrait;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Unit tests for the VaGovFormBuilderController class.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Controller\VaGovFormBuilderController
 */
class VaGovFormBuilderControllerTest extends VaGovExistingSiteBase {

  use ParagraphCreationTrait;

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * The controller instance.
   *
   * @var \Drupal\va_gov_form_builder\Controller\VaGovFormBuilderController
   */
  protected $controller;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $entityTypeManager = \Drupal::service('entity_type.manager');
    $drupalFormBuilder = \Drupal::service('form_builder');
    $digitalFormsService = new DigitalFormsService($entityTypeManager);
    $sessionService = \Drupal::service('session');

    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $entityTypeManager);
    $container->set('form_builder', $drupalFormBuilder);
    $container->set('va_gov_form_builder.digital_forms_service', $digitalFormsService);
    $container->set('session', $sessionService);

    // Create the controller instance.
    $this->controller = VaGovFormBuilderController::create($container);
  }

  /**
   * Tests css is included.
   */
  public function testCssIncluded() {
    $page = $this->controller->home();

    $this->assertContains(
      'va_gov_form_builder/form_builder',
      $page['#attached']['library'],
      "The library 'va_gov_form_builder/form_builder' is successfully attached."
    );
  }

  /**
   * Tests the entry method redirects correctly.
   */
  public function testEntryRedirect() {
    $response = $this->controller->entry();

    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertStringContainsString(Url::fromRoute('va_gov_form_builder.home')->toString(), $response->getTargetUrl());
  }

  /**
   * Tests the home method returns a Home page.
   */
  public function testHome() {
    $page = $this->controller->home();

    $this->assertArrayHasKey('content', $page);
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__home', $page['content']['#theme']);

    // Ensure css is added.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/home', $page['#attached']['library']);
  }

  /**
   * Tests the formInfo method returns a FormInfo form in create mode.
   *
   * When $nid is not passed (is NULL), it should be create mode.
   */
  public function testFormInfoCreate() {
    $page = $this->controller->formInfo();

    // In create mode, default value should be null.
    $this->assertArrayHasKey('#default_value', $page['content']['title']);
    $this->assertEmpty($page['content']['title']['#default_value']);

    // Ensure css is added.
    // This should be present on both create and edit mode.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/form_info', $page['#attached']['library']);

  }

  /**
   * Tests the formInfo method returns a FormInfo form in edit mode.
   *
   * When $nid is passed, it should be edit mode.
   */
  public function testFormInfoEdit() {
    $title = 'Test Digital Form ' . uniqid();
    $formNumber = '99-9999';

    // Create a new Digital Form node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'title' => $title,
      'field_chapters' => [],
      'field_va_form_number' => $formNumber,
    ]);

    $nid = $node->id();
    $page = $this->controller->formInfo($nid);

    // In edit mode, default value should be populated.
    $this->assertArrayHasKey('#default_value', $page['content']['title']);
    $this->assertEquals($title, $page['content']['title']['#default_value']);

    // Ensure css is added.
    // This should be present on both create and edit mode.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/form_info', $page['#attached']['library']);
  }

  /**
   * Tests the formInfo method throws an exception with a bad node id.
   */
  public function testFormInfoException() {
    $someNonExistentNodeId = '9999999999999';

    $this->expectException(NotFoundHttpException::class);
    $this->controller->formInfo($someNonExistentNodeId);
  }

  /**
   * Tests the layout method returns a Layout page.
   */
  public function testLayout() {
    $title = 'Test Digital Form ' . uniqid();
    $formNumber = '99-9999';

    // Create a new Digital Form node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'title' => $title,
      'field_chapters' => [],
      'field_va_form_number' => $formNumber,
    ]);

    // Add paragraphs.
    // Contact information.
    $contactInfoParagraph = \Drupal::entityTypeManager()->getStorage('paragraph')->create([
      'type' => 'digital_form_phone_and_email',
      'field_title' => 'Contact information',
      'field_include_email' => TRUE,
    ]);
    $node->get('field_chapters')->appendItem($contactInfoParagraph);
    // List and loop.
    $listAndLoopParagraph = \Drupal::entityTypeManager()->getStorage('paragraph')->create([
      'type' => 'digital_form_list_loop',
      'field_title' => 'Your employers',
    ]);
    $node->get('field_chapters')->appendItem($listAndLoopParagraph);

    // Save node.
    $node->save();

    $page = $this->controller->layout($node->id());

    $this->assertArrayHasKey('content', $page);
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__layout', $page['content']['#theme']);

    // Ensure step statuses are calculated correctly.
    // --> Contact info should be "complete" since paragraph exists.
    $this->assertEquals('complete', $page['content']['#contact_info']['status'], 'Contact info is complete');
    // --> Address info should be "incomplete" since paragraph does not exist.
    $this->assertEquals('incomplete', $page['content']['#address_info']['status'], 'Address info is incomplete');

    // Ensure additional steps are included and have "complete" status.
    $this->assertArrayHasKey('#additional_steps', $page['content']);
    $this->assertEquals('complete', $page['content']['#additional_steps']['steps'][0]['status']);

    // Ensure css is added.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/layout', $page['#attached']['library']);
  }

  /**
   * Tests the nameAndDob method returns a Name-and-date-of-birth page.
   */
  public function testNameAndDob() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->nameAndDob($node->id());

    $this->assertArrayHasKey('content', $page);
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__name_and_dob', $page['content']['#theme']);

    // Ensure variables are set as expected.
    // Preview image.
    $this->assertArrayHasKey('#preview', $page['content']);
    $this->assertArrayHasKey('alt_text', $page['content']['#preview']);
    $this->assertEquals('Name-and-date-of-birth preview', $page['content']['#preview']['alt_text']);
    $this->assertArrayHasKey('url', $page['content']['#preview']);
    $this->assertStringContainsString('name-and-dob.png', $page['content']['#preview']['url']);
    // Buttons.
    $this->assertArrayHasKey('#buttons', $page['content']);
    // Primary button.
    $this->assertArrayHasKey('primary', $page['content']['#buttons']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['primary']);
    $this->assertEquals('Save and continue', $page['content']['#buttons']['primary']['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['primary']);
    $this->assertStringContainsString("/form-builder/{$node->id()}", $page['content']['#buttons']['primary']['url']);
    // Secondary buttons.
    $this->assertArrayHasKey('secondary', $page['content']['#buttons']);
    $this->assertCount(1, $page['content']['#buttons']['secondary']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['secondary'][0]);
    $this->assertEquals('Next page', $page['content']['#buttons']['secondary'][0]['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['secondary'][0]);
    $this->assertStringContainsString("/form-builder/{$node->id()}/identification-info", $page['content']['#buttons']['secondary'][0]['url']);

    // Ensure css is added as expected.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/single_column_with_buttons', $page['#attached']['library']);
    $this->assertContains('va_gov_form_builder/non_editable_pattern', $page['#attached']['library']);
  }

  /**
   * Tests the identificationInfo method returns an Identification-info page.
   */
  public function testIdentificationInfo() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->identificationInfo($node->id());

    $this->assertArrayHasKey('content', $page);

    // Ensure theme is set as expected.
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__identification_info', $page['content']['#theme']);

    // Ensure variables are set as expected.
    // Preview image.
    $this->assertArrayHasKey('#preview', $page['content']);
    $this->assertArrayHasKey('alt_text', $page['content']['#preview']);
    $this->assertEquals('Identification-information preview', $page['content']['#preview']['alt_text']);
    $this->assertArrayHasKey('url', $page['content']['#preview']);
    $this->assertStringContainsString('identification-info.png', $page['content']['#preview']['url']);
    // Buttons.
    $this->assertArrayHasKey('#buttons', $page['content']);
    // Primary button.
    $this->assertArrayHasKey('primary', $page['content']['#buttons']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['primary']);
    $this->assertEquals('Save and continue', $page['content']['#buttons']['primary']['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['primary']);
    $this->assertStringContainsString("/form-builder/{$node->id()}", $page['content']['#buttons']['primary']['url']);
    // Secondary buttons.
    $this->assertArrayHasKey('secondary', $page['content']['#buttons']);
    $this->assertCount(1, $page['content']['#buttons']['secondary']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['secondary'][0]);
    $this->assertEquals('Previous page', $page['content']['#buttons']['secondary'][0]['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['secondary'][0]);
    $this->assertStringContainsString("/form-builder/{$node->id()}/name-and-dob", $page['content']['#buttons']['secondary'][0]['url']);

    // Ensure css is added as expected.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/single_column_with_buttons', $page['#attached']['library']);
    $this->assertContains('va_gov_form_builder/non_editable_pattern', $page['#attached']['library']);
  }

  /**
   * Tests the addressInfo method returns an Address-info page.
   */
  public function testAddressInfo() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->addressInfo($node->id());

    $this->assertArrayHasKey('content', $page);

    // Ensure theme is set as expected.
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__address_info', $page['content']['#theme']);

    // Ensure variables are set as expected.
    // Preview image.
    $this->assertArrayHasKey('#preview', $page['content']);
    $this->assertArrayHasKey('alt_text', $page['content']['#preview']);
    $this->assertEquals('Address-information preview', $page['content']['#preview']['alt_text']);
    $this->assertArrayHasKey('url', $page['content']['#preview']);
    $this->assertStringContainsString('address-info.png', $page['content']['#preview']['url']);
    // Buttons.
    $this->assertArrayHasKey('#buttons', $page['content']);
    // Primary button.
    $this->assertArrayHasKey('primary', $page['content']['#buttons']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['primary']);
    $this->assertEquals('Save and continue', $page['content']['#buttons']['primary']['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['primary']);
    $this->assertStringContainsString("/form-builder/{$node->id()}", $page['content']['#buttons']['primary']['url']);

    // Ensure css is added as expected.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/single_column_with_buttons', $page['#attached']['library']);
    $this->assertContains('va_gov_form_builder/non_editable_pattern', $page['#attached']['library']);
  }

  /**
   * Tests the contactInfo method returns a Contact-info page.
   */
  public function testContactInfo() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->contactInfo($node->id());

    $this->assertArrayHasKey('content', $page);

    // Ensure theme is set as expected.
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__contact_info', $page['content']['#theme']);

    // Ensure variables are set as expected.
    // Preview image.
    $this->assertArrayHasKey('#preview', $page['content']);
    $this->assertArrayHasKey('alt_text', $page['content']['#preview']);
    $this->assertEquals('Contact-information preview', $page['content']['#preview']['alt_text']);
    $this->assertArrayHasKey('url', $page['content']['#preview']);
    $this->assertStringContainsString('contact-info.png', $page['content']['#preview']['url']);
    // Buttons.
    $this->assertArrayHasKey('#buttons', $page['content']);
    // Primary button.
    $this->assertArrayHasKey('primary', $page['content']['#buttons']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['primary']);
    $this->assertEquals('Save and continue', $page['content']['#buttons']['primary']['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['primary']);
    $this->assertStringContainsString("/form-builder/{$node->id()}", $page['content']['#buttons']['primary']['url']);
    // Ensure css is added as expected.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/single_column_with_buttons', $page['#attached']['library']);
    $this->assertContains('va_gov_form_builder/non_editable_pattern', $page['#attached']['library']);
  }

  /**
   * Tests the stepLabel method returns a StepLabel form in create mode.
   *
   * When $stepParagraphId is not passed (is NULL), it should be create mode.
   */
  public function testStepLabelCreate() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->stepLabel($node->id());

    // In create mode, default value should be null.
    $this->assertArrayHasKey('#default_value', $page['content']['field_title']);
    $this->assertEmpty($page['content']['field_title']['#default_value']);

    // Ensure css is added.
    // This should be present on both create and edit mode.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/step_label', $page['#attached']['library']);

  }

  /**
   * Tests the stepLabel method returns a StepLabel form in edit mode.
   *
   * When $stepParagraphId is passed, it should be edit mode.
   */
  public function testStepLabelEdit() {
    // Create a new custom-step paragraph.
    $paragraphTitle = 'Custom Step ' . uniqid();
    $paragraph = $this->createParagraph([
      'type' => 'digital_form_custom_step',
      'field_title' => $paragraphTitle,
    ]);
    $paragraph->save();

    // Create a new Digital Form node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'title' => 'Test Digital Form ' . uniqid(),
      'field_chapters' => [$paragraph],
      'field_va_form_number' => '99-9999',
    ]);
    $node->save();

    $nid = $node->id();
    $paragraphId = $paragraph->id();
    $page = $this->controller->stepLabel($nid, $paragraphId);

    // In edit mode, default value should be populated.
    $this->assertArrayHasKey('#default_value', $page['content']['field_title']);
    $this->assertEquals($paragraphTitle, $page['content']['field_title']['#default_value']);

    // Ensure css is added.
    // This should be present on both create and edit mode.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/step_label', $page['#attached']['library']);
  }

  /**
   * Tests the reviewAndSign method returns a Review-and-Sign page.
   */
  public function testReviewAndSign() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->reviewAndSign($node->id());

    $this->assertArrayHasKey('content', $page);
    // Ensure theme is set as expected.
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertEquals('page_content__va_gov_form_builder__review_and_sign', $page['content']['#theme']);

    // Ensure variables are set as expected.
    // Preview image.
    $this->assertArrayHasKey('#preview', $page['content']);
    $this->assertArrayHasKey('alt_text', $page['content']['#preview']);
    $this->assertEquals('Statement-of-truth preview', $page['content']['#preview']['alt_text']);
    $this->assertArrayHasKey('url', $page['content']['#preview']);
    $this->assertStringContainsString('statement-of-truth.png', $page['content']['#preview']['url']);
    // Buttons.
    $this->assertArrayHasKey('#buttons', $page['content']);
    // Primary button.
    $this->assertArrayHasKey('primary', $page['content']['#buttons']);
    $this->assertArrayHasKey('label', $page['content']['#buttons']['primary']);
    $this->assertEquals('Save and continue', $page['content']['#buttons']['primary']['label']);
    $this->assertArrayHasKey('url', $page['content']['#buttons']['primary']);
    $this->assertStringContainsString("/form-builder/{$node->id()}", $page['content']['#buttons']['primary']['url']);

    // Ensure css is added as expected.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/single_column_with_buttons', $page['#attached']['library']);
    $this->assertContains('va_gov_form_builder/non_editable_pattern', $page['#attached']['library']);
  }

  /**
   * Tests the stepStyle method returns a StepStyle form.
   */
  public function testStepStyle() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    // Ensure the expected session variable is populated.
    $session = \Drupal::service('session');
    $session->set('form_builder:add_step:step_label', 'Some non-empty value');

    // Call the controller method.
    $page = $this->controller->stepStyle($node->id());

    // Ensure css is added.
    // This should be present on both create and edit mode.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/step_style', $page['#attached']['library']);
  }

  /**
   * Tests the stepStyle method returns a redirect.
   *
   * When session variable is empty, it should redirect.
   */
  public function testStepStyleRedirect() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    // Ensure the expected session variable is empty.
    $session = \Drupal::service('session');
    $session->set('form_builder:add_step:step_label', NULL);

    // Call the controller method.
    $response = $this->controller->stepStyle($node->id());

    // Ensure the returned value is a redirect.
    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertStringContainsString(
      Url::fromRoute(
        'va_gov_form_builder.step.step_label.create',
        ['nid' => $node->id()]
      )->toString(),
      $response->getTargetUrl()
    );
  }

  /**
   * Tests the viewForm method returns a View-form page.
   */
  public function testViewForm() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->viewForm($node->id());

    $this->assertArrayHasKey('content', $page);
    // Ensure theme is set as expected.
    $this->assertArrayHasKey('#theme', $page['content']);
    $this->assertStringContainsSTring('page_content__va_gov_form_builder__view_form', $page['content']['#theme']);

    // Ensure variables are set as expected.
    // Buttons.
    $this->assertArrayHasKey('#buttons', $page['content']);
    // Primary button.
    // @todo Update buttons test when staging-url field is added.
    $this->assertArrayHasKey('primary', $page['content']['#buttons']);

    // Ensure css is added as expected.
    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/single_column_with_buttons', $page['#attached']['library']);
  }

}
