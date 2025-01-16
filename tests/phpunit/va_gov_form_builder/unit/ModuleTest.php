<?php

namespace tests\phpunit\va_gov_form_builder\unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
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
   * Set up the environment for each test.
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
    // Call the function to test.
    $result = va_gov_form_builder_theme(
      NULL,
      NULL,
      NULL,
      $this->modulePath
    );

    // Assert the expected theme definition exists.
    // Page (wrapper) theme.
    $this->assertArrayHasKey('page__va_gov_form_builder', $result);
    $this->assertEquals('page', $result['page__va_gov_form_builder']['base hook']);
    $this->assertEquals($this->modulePath . '/templates/page', $result['page__va_gov_form_builder']['path']);

    // Page-content themes.
    $page_content_theme_prefix = 'page_content__va_gov_form_builder__';
    $page_content_theme_path = $this->modulePath . '/templates/page-content';
    // Home page.
    $this->assertArrayHasKey($page_content_theme_prefix . 'home', $result);
    $this->assertEquals($page_content_theme_path, $result[$page_content_theme_prefix . 'home']['path']);
    $this->assertArrayHasKey('variables', $result[$page_content_theme_prefix . 'home']);
    $this->assertArrayHasKey('recent_forms', $result[$page_content_theme_prefix . 'home']['variables']);
    $this->assertArrayHasKey('build_form_url', $result[$page_content_theme_prefix . 'home']['variables']);
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
    $this->assertContains('page__va_gov_form_builder', $suggestions);
  }

}
