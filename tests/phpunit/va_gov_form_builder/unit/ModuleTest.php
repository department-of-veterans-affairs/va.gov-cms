<?php

namespace tests\phpunit\va_gov_form_builder\unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Routing\RouteMatchInterface;
use DrupalFinder\DrupalFinder;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for va_gov_form_builder.module.
 *
 * @group unit
 * @group all
 */
class ModuleTest extends VaGovUnitTestBase {

  /**
   * The path to the module being tested.
   *
   * @var string
   */
  private $modulePath = 'modules/custom/va_gov_form_builder';

  /**
   * A mock service container.
   *
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  private $container;

  /**
   * Setup the environment for each test.
   */
  protected function setUp(): void {
    parent::setUp();

    // Find Drupal root.
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(__DIR__);
    $drupalRoot = $drupalFinder->getDrupalRoot();

    // Require the module file so we can test it.
    require_once $drupalRoot . '/' . $this->modulePath . '/va_gov_form_builder.module';

    // Create the mock service container.
    $this->container = new ContainerBuilder();

    // Set the mocked container as the global Drupal container.
    \Drupal::setContainer($this->container);
  }

  /**
   * Tests va_gov_form_builder_theme().
   *
   * @covers ::va_gov_form_builder_theme
   */
  public function testVaGovFormBuilderHookTheme() {
    // Mock the extension.list.module service and add to the container.
    $extensionListMock = $this->createMock(ModuleExtensionList::class);
    $extensionListMock->expects($this->once())
      ->method('getPath')
      ->with('va_gov_form_builder')
      ->willReturn($this->modulePath);
    $this->container->set('extension.list.module', $extensionListMock);

    // Call the function to test.
    $result = va_gov_form_builder_theme();

    // Assert the expected theme definition exists.
    $this->assertArrayHasKey('va_gov_form_builder_page', $result);
    $this->assertEquals('page--va-gov-form-builder', $result['va_gov_form_builder_page']['template']);
    $this->assertEquals($this->modulePath . '/templates', $result['va_gov_form_builder_page']['path']);
  }

  /**
   * Tests va_gov_form_builder_theme_suggestions_page().
   *
   * @covers va_gov_form_builder_theme_suggestions_page
   */
  public function testVaGovFormBuilderThemeSuggestionsPage() {
    // Ensure *any* route starting with `va_gov_form_builder.` returns the
    // expected theme suggestions.
    $exampleRoute = 'va_gov_form_builder.example_route';

    // Mock the current_route_match service and add to the container.
    $currentRouteMatchMock = $this->createMock(RouteMatchInterface::class);
    $currentRouteMatchMock->expects($this->once())
      ->method('getRouteName')
      ->willReturn($exampleRoute);
    $this->container->set('current_route_match', $currentRouteMatchMock);

    // Call the function to test.
    $variables = [];
    $suggestions = va_gov_form_builder_theme_suggestions_page($variables);

    // Assert the expected theme suggestion is returned.
    $this->assertContains('va_gov_form_builder_page', $suggestions);
  }

}
