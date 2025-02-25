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
    // 1. Home page.
    $homeTheme = $page_content_theme_prefix . 'home';
    $this->assertArrayHasKey($homeTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$homeTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$homeTheme]);
    $this->assertArrayHasKey('recent_forms', $result[$homeTheme]['variables']);
    $this->assertArrayHasKey('build_form_url', $result[$homeTheme]['variables']);
    // 2. Layout page.
    $layoutTheme = $page_content_theme_prefix . 'layout';
    $this->assertArrayHasKey($layoutTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$layoutTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$layoutTheme]);
    $this->assertArrayHasKey('form_info', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('intro', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('your_personal_info', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('address_info', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('contact_info', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('additional_steps', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('review_and_sign', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('confirmation', $result[$layoutTheme]['variables']);
    $this->assertArrayHasKey('view_form', $result[$layoutTheme]['variables']);
    // 3. Non-editable-pattern-step pages.
    // 3a. Name-and-date-of-birth page.
    $nameAndDobTheme = $page_content_theme_prefix . 'name_and_dob';
    $this->assertArrayHasKey($nameAndDobTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$nameAndDobTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$nameAndDobTheme]);
    $this->assertArrayHasKey('preview', $result[$nameAndDobTheme]['variables']);
    $this->assertArrayHasKey('alt_text', $result[$nameAndDobTheme]['variables']['preview']);
    $this->assertArrayHasKey('url', $result[$nameAndDobTheme]['variables']['preview']);
    $this->assertArrayHasKey('primary_button', $result[$nameAndDobTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$nameAndDobTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('url', $result[$nameAndDobTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('secondary_button', $result[$nameAndDobTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$nameAndDobTheme]['variables']['secondary_button']);
    $this->assertArrayHasKey('url', $result[$nameAndDobTheme]['variables']['secondary_button']);
    // 3b. Identification-information page.
    $identificationInfoTheme = $page_content_theme_prefix . 'identification_info';
    $this->assertArrayHasKey($identificationInfoTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$identificationInfoTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$identificationInfoTheme]);
    $this->assertArrayHasKey('preview', $result[$identificationInfoTheme]['variables']);
    $this->assertArrayHasKey('alt_text', $result[$identificationInfoTheme]['variables']['preview']);
    $this->assertArrayHasKey('url', $result[$identificationInfoTheme]['variables']['preview']);
    $this->assertArrayHasKey('primary_button', $result[$identificationInfoTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$identificationInfoTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('url', $result[$identificationInfoTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('secondary_button', $result[$identificationInfoTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$identificationInfoTheme]['variables']['secondary_button']);
    $this->assertArrayHasKey('url', $result[$identificationInfoTheme]['variables']['secondary_button']);
    // 3c. Address-information page.
    $addressInfoTheme = $page_content_theme_prefix . 'address_info';
    $this->assertArrayHasKey($addressInfoTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$addressInfoTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$addressInfoTheme]);
    $this->assertArrayHasKey('preview', $result[$addressInfoTheme]['variables']);
    $this->assertArrayHasKey('alt_text', $result[$addressInfoTheme]['variables']['preview']);
    $this->assertArrayHasKey('url', $result[$addressInfoTheme]['variables']['preview']);
    $this->assertArrayHasKey('primary_button', $result[$addressInfoTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$addressInfoTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('url', $result[$addressInfoTheme]['variables']['primary_button']);
    // 3d. Contact-information page.
    $contactInfoTheme = $page_content_theme_prefix . 'contact_info';
    $this->assertArrayHasKey($contactInfoTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$contactInfoTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$contactInfoTheme]);
    $this->assertArrayHasKey('preview', $result[$contactInfoTheme]['variables']);
    $this->assertArrayHasKey('alt_text', $result[$contactInfoTheme]['variables']['preview']);
    $this->assertArrayHasKey('url', $result[$contactInfoTheme]['variables']['preview']);
    $this->assertArrayHasKey('primary_button', $result[$contactInfoTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$contactInfoTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('url', $result[$contactInfoTheme]['variables']['primary_button']);
    // 3e. Review-and-Sign page.
    $reviewAndSignTheme = $page_content_theme_prefix . 'review_and_sign';
    $this->assertArrayHasKey($reviewAndSignTheme, $result);
    $this->assertEquals($page_content_theme_path, $result[$reviewAndSignTheme]['path']);
    $this->assertArrayHasKey('variables', $result[$reviewAndSignTheme]);
    $this->assertArrayHasKey('preview', $result[$reviewAndSignTheme]['variables']);
    $this->assertArrayHasKey('alt_text', $result[$reviewAndSignTheme]['variables']['preview']);
    $this->assertArrayHasKey('url', $result[$reviewAndSignTheme]['variables']['preview']);
    $this->assertArrayHasKey('primary_button', $result[$reviewAndSignTheme]['variables']);
    $this->assertArrayHasKey('label', $result[$reviewAndSignTheme]['variables']['primary_button']);
    $this->assertArrayHasKey('url', $result[$reviewAndSignTheme]['variables']['primary_button']);

    // Form themes.
    $form_theme_prefix = 'form__va_gov_form_builder__';
    $form_theme_path = $this->modulePath . '/templates/form';
    // Assert all items in array exist.
    $form_themes = ['form_info'];
    foreach ($form_themes as $form_theme) {
      $this->assertArrayHasKey($form_theme_prefix . $form_theme, $result);
      $this->assertEquals($form_theme_path, $result[$form_theme_prefix . $form_theme]['path']);
      $this->assertEquals('form', $result[$form_theme_prefix . $form_theme]['render element']);
    }
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
