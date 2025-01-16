<?php

namespace tests\phpunit\va_gov_form_builder\functional\Controller;

use Drupal\Core\Url;
use Drupal\va_gov_form_builder\Controller\VaGovFormBuilderController;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    $this->assertArrayHasKey('#attached', $page);
    $this->assertContains('va_gov_form_builder/va_gov_form_builder_styles__home', $page['#attached']['library']);
  }

  /**
   * Tests the startConversion method returns a StartConversion form.
   */
  public function testStartConversion() {
    $page = $this->controller->startConversion();

    $this->assertArrayHasKey('content', $page);
    $this->assertArrayHasKey('start_new_conversion_header', $page['content']);
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
