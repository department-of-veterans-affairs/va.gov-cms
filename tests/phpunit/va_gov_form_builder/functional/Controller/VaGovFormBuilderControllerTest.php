<?php

namespace tests\phpunit\va_gov_form_builder\functional\Controller;

use Drupal\Core\Url;
use Drupal\va_gov_form_builder\Controller\VaGovFormBuilderController;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    $container = new ContainerBuilder();

    // Add Drupal's form builder to the service container.
    $container->set('form_builder', \Drupal::formBuilder());

    // Add our DigitalFormsService to the service container.
    $digitalFormsService = new DigitalFormsService(\Drupal::service('entity_type.manager'));
    $container->set('va_gov_form_builder.digital_forms_service', $digitalFormsService);

    // Create the controller instance.
    $this->controller = VaGovFormBuilderController::create($container);
  }

  /**
   * Tests css is included.
   */
  public function testCssIncluded() {
    $page = $this->controller->home();

    $this->assertContains(
      'va_gov_form_builder/va_gov_form_builder_styles',
      $page['#attached']['library'],
      "The library 'va_gov_form_builder/va_gov_form_builder_styles' is successfully attached."
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
    $this->assertContains('va_gov_form_builder/va_gov_form_builder_styles__home', $page['#attached']['library']);
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
    $this->assertContains('va_gov_form_builder/va_gov_form_builder_styles__form_info', $page['#attached']['library']);

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
    $this->assertContains('va_gov_form_builder/va_gov_form_builder_styles__form_info', $page['#attached']['library']);
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
   * Tests the nameAndDob method returns a NameAndDob form.
   */
  public function testNameAndDob() {
    // Create a node.
    $node = $this->createNode([
      'type' => 'digital_form',
      'field_chapters' => [],
    ]);

    $page = $this->controller->nameAndDob($node->id());

    $this->assertArrayHasKey('content', $page);
    $this->assertArrayHasKey('name_and_dob_header', $page['content']);
  }

}
