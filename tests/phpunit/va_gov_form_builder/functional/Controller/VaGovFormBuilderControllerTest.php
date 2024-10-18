<?php

namespace tests\phpunit\va_gov_form_builder\functional\Controller;

use Drupal\Core\Url;
use Drupal\va_gov_form_builder\Controller\VaGovFormBuilderController;
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
    $container->set('form_builder', \Drupal::formBuilder());
    $this->controller = VaGovFormBuilderController::create($container);
  }

  /**
   * Tests the entry method redirects correctly.
   */
  public function testEntryRedirect() {
    $response = $this->controller->entry();

    $this->assertInstanceOf(RedirectResponse::class, $response);
    $this->assertStringContainsString(Url::fromRoute('va_gov_form_builder.intro')->toString(), $response->getTargetUrl());
  }

  /**
   * Tests the intro method returns an Intro form.
   */
  public function testIntro() {
    $form = $this->controller->intro();

    $this->assertArrayHasKey('#type', $form);
    $this->assertArrayHasKey('working_with_form_builder_header', $form);
  }

  /**
   * Tests the startConversion method returns a StartConversion form.
   */
  public function testStartConversion() {
    $form = $this->controller->startConversion();

    $this->assertArrayHasKey('#type', $form);
    $this->assertArrayHasKey('start_new_conversion_header', $form);
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

    $form = $this->controller->nameAndDob($node->id());

    $this->assertArrayHasKey('#type', $form);
    $this->assertArrayHasKey('name_and_dob_header', $form);
  }

}
