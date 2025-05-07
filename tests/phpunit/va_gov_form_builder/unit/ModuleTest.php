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
   */
  const MODULE_PATH = 'modules/custom/va_gov_form_builder';

  /**
   * The path to the template directory.
   */
  const TEMPLATE_PATH = self::MODULE_PATH . '/templates';

  /**
   * The path to the page template directory.
   */
  const PAGE_TEMPLATE_PATH = self::TEMPLATE_PATH . '/page';

  /**
   * The path to the page-content template directory.
   */
  const PAGE_CONTENT_TEMPLATE_PATH = self::TEMPLATE_PATH . '/page-content';

  /**
   * The path to the form template directory.
   */
  const FORM_TEMPLATE_PATH = self::TEMPLATE_PATH . '/form';

  /**
   * The path to the form-element template directory.
   */
  const FORM_ELEMENT_TEMPLATE_PATH = self::TEMPLATE_PATH . '/form-elements';

  /**
   * The path to the page-element template directory.
   */
  const PAGE_ELEMENT_TEMPLATE_PATH = self::TEMPLATE_PATH . '/page-elements';

  /**
   * The prefix for page-content themes.
   */
  const PAGE_CONTENT_THEME_PREFIX = 'page_content__va_gov_form_builder__';

  /**
   * The prefix for form themes.
   */
  const FORM_THEME_PREFIX = 'form__va_gov_form_builder__';

  /**
   * The prefix for form-element themes.
   */
  const FORM_ELEMENT_THEME_PREFIX = 'form_element__va_gov_form_builder__';

  /**
   * The prefix for page-element themes.
   */
  const PAGE_ELEMENT_THEME_PREFIX = 'page_element__va_gov_form_builder__';

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
    require_once $drupalRoot . '/' . self::MODULE_PATH . '/va_gov_form_builder.module';

    // Create the mock service container.
    $this->container = new ContainerBuilder();

    // Set the mocked container as the global Drupal container.
    \Drupal::setContainer($this->container);
  }

  /**
   * Helper function to assert a form theme.
   *
   * @param array $theme
   *   The key of the theme entry to check. This should be
   *   in snake-case.
   * @param array $themeEntries
   *   The array of theme entries returned from hook_theme.
   */
  private function assertTheme($theme, $themeEntries) {
    // Assert the theme exists.
    $this->assertArrayHasKey($theme, $themeEntries);

    // Assert the path key exists and is set to the expected value.
    $this->assertArrayHasKey('path', $themeEntries[$theme]);
    $this->assertEquals(self::PAGE_CONTENT_TEMPLATE_PATH, $themeEntries[$theme]['path']);

    // Assert the template key exists and is set to the expected value.
    $themeWithoutPrefix = str_replace(self::PAGE_CONTENT_THEME_PREFIX, '', $theme);
    $kebabCaseTheme = str_replace('_', '-', $themeWithoutPrefix);
    $this->assertArrayHasKey('template', $themeEntries[$theme]);
    $this->assertEquals($kebabCaseTheme, $themeEntries[$theme]['template']);
  }

  /**
   * Helper function to make assertions on a non-editable-pattern theme.
   *
   * @param array $theme
   *   The key of the theme entry to check. This should be
   *   in snake-case.
   * @param array $themeEntries
   *   The array of theme entries returned from hook_theme.
   */
  private function assertNonEditablePatternTheme($theme, $themeEntries) {
    // Assert common properties.
    $this->assertTheme($theme, $themeEntries);

    // Assert variables exist.
    $this->assertArrayHasKey('variables', $themeEntries[$theme]);
    $this->assertArrayHasKey('preview', $themeEntries[$theme]['variables']);
    $this->assertArrayHasKey('buttons', $themeEntries[$theme]['variables']);
  }

  /**
   * Helper function to make assertions on a view-form theme.
   *
   * @param array $theme
   *   The key of the theme entry to check. This should be
   *   in snake-case.
   * @param array $themeEntries
   *   The array of theme entries returned from hook_theme.
   */
  private function assertViewFormTheme($theme, $themeEntries) {
    // Assert common properties.
    $this->assertTheme($theme, $themeEntries);

    // Assert variables exist.
    $this->assertArrayHasKey('variables', $themeEntries[$theme]);
    $this->assertArrayHasKey('buttons', $themeEntries[$theme]['variables']);
  }

  /**
   * Helper function to make assertions on a step-layout theme.
   *
   * @param array $theme
   *   The key of the theme entry to check. This should be
   *   in snake-case.
   * @param array $themeEntries
   *   The array of theme entries returned from hook_theme.
   */
  private function assertStepLayoutTheme($theme, $themeEntries) {
    // Assert common properties.
    $this->assertTheme($theme, $themeEntries);

    // Assert variables exist.
    $this->assertArrayHasKey('variables', $themeEntries[$theme]);
    $this->assertArrayHasKey('step_label', $themeEntries[$theme]['variables']);
    $this->assertArrayHasKey('pages', $themeEntries[$theme]['variables']);
    $this->assertArrayHasKey('buttons', $themeEntries[$theme]['variables']);
  }

  /**
   * Helper function to assert on a custom-or-predefined-question theme.
   *
   * @param array $theme
   *   The key of the theme entry to check. This should be
   *   in snake-case.
   * @param array $themeEntries
   *   The array of theme entries returned from hook_theme.
   */
  private function assertCustomOrPredefinedQuestionTheme($theme, $themeEntries) {
    // Assert common properties.
    $this->assertTheme($theme, $themeEntries);

    // Assert variables exist.
    $this->assertArrayHasKey('variables', $themeEntries[$theme]);
    $this->assertArrayHasKey('predefined_questions', $themeEntries[$theme]['variables']);
    $this->assertArrayHasKey('buttons', $themeEntries[$theme]['variables']);
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
      self::MODULE_PATH
    );

    // Assert the expected theme definition exists.
    // Page (wrapper) theme.
    $this->assertArrayHasKey('page__va_gov_form_builder', $result);
    $this->assertEquals('page', $result['page__va_gov_form_builder']['base hook']);
    $this->assertEquals(self::PAGE_TEMPLATE_PATH, $result['page__va_gov_form_builder']['path']);

    // Page-content themes.
    // 1. Home page.
    $homeTheme = self::PAGE_CONTENT_THEME_PREFIX . 'home';
    $this->assertArrayHasKey($homeTheme, $result);
    $this->assertArrayHasKey('path', $result[$homeTheme]);
    $this->assertEquals(self::PAGE_CONTENT_TEMPLATE_PATH, $result[$homeTheme]['path']);
    $this->assertArrayHasKey('template', $result[$homeTheme]);
    $this->assertEquals('home', $result[$homeTheme]['template']);
    $this->assertArrayHasKey('variables', $result[$homeTheme]);
    $this->assertArrayHasKey('recent_forms', $result[$homeTheme]['variables']);
    $this->assertArrayHasKey('build_form_url', $result[$homeTheme]['variables']);
    // 2. Layout page.
    $layoutTheme = self::PAGE_CONTENT_THEME_PREFIX . 'layout';
    $this->assertArrayHasKey($layoutTheme, $result);
    $this->assertArrayHasKey('path', $result[$layoutTheme]);
    $this->assertEquals(self::PAGE_CONTENT_TEMPLATE_PATH, $result[$layoutTheme]['path']);
    $this->assertArrayHasKey('template', $result[$layoutTheme]);
    $this->assertEquals('layout', $result[$layoutTheme]['template']);
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
    // 3. Non-editable pattern pages.
    $nonEditablePatternPages = [
      'name_and_dob',
      'identification_info',
      'address_info',
      'contact_info',
      'employment_history',
      'review_and_sign',
    ];
    foreach ($nonEditablePatternPages as $nonEditablePatternPage) {
      $nonEditablePatternPageTheme = self::PAGE_CONTENT_THEME_PREFIX . $nonEditablePatternPage;
      $this->assertNonEditablePatternTheme($nonEditablePatternPageTheme, $result);
    }
    // 4. View-form pages.
    // 4a. View-form page when viewing form is available.
    $this->assertViewFormTheme(self::PAGE_CONTENT_THEME_PREFIX . 'view_form__available', $result);
    // 4b. View-form page when viewing form is unavailable.
    $this->assertViewFormTheme(self::PAGE_CONTENT_THEME_PREFIX . 'view_form__unavailable', $result);
    // 5. Step-layout page.
    // 5a. Step-layout page for single question.
    $this->assertStepLayoutTheme(self::PAGE_CONTENT_THEME_PREFIX . 'step_layout__single_question', $result);
    // 5b. Step-layout page for repeating set.
    $this->assertStepLayoutTheme(self::PAGE_CONTENT_THEME_PREFIX . 'step_layout__repeating_set', $result);
    // 6. Step-style page.
    $stepStyleTheme = self::PAGE_CONTENT_THEME_PREFIX . 'step_style';
    $this->assertArrayHasKey($stepStyleTheme, $result);
    $this->assertArrayHasKey('path', $result[$stepStyleTheme]);
    $this->assertEquals(self::PAGE_CONTENT_TEMPLATE_PATH, $result[$stepStyleTheme]['path']);
    $this->assertArrayHasKey('template', $result[$stepStyleTheme]);
    $this->assertEquals('step-style', $result[$stepStyleTheme]['template']);
    $this->assertArrayHasKey('variables', $result[$stepStyleTheme]);
    $this->assertArrayHasKey('step_label', $result[$stepStyleTheme]['variables']);
    $this->assertArrayHasKey('preview', $result[$stepStyleTheme]['variables']);
    $this->assertArrayHasKey('buttons', $result[$stepStyleTheme]['variables']);
    // 7. Custom-or-predefined-question page.
    // 7a. Custom-or-predefined-question page for single question.
    $this->assertCustomOrPredefinedQuestionTheme(
      self::PAGE_CONTENT_THEME_PREFIX . 'custom_or_predefined_question__single_question',
      $result
    );
    // 7b. Custom-or-predefined-question page for repeating set.
    $this->assertCustomOrPredefinedQuestionTheme(
      self::PAGE_CONTENT_THEME_PREFIX . 'custom_or_predefined_question__repeating_set',
      $result
    );

    // Form themes.
    $form_themes = [
      'form_info',
      'step_label',
      'custom_or_predefined__single_question',
      'custom_or_predefined__repeating_set',
      'response_kind',
      'date_type',
      'custom_single_question_page_title',
      'custom_single_question_single_date_response',
      'custom_single_question_date_range_response',
      'custom_single_question_text_input_response',
      'custom_single_question_text_area_response',
      'custom_single_question_radio_response',
      'custom_single_question_checkbox_response',
    ];
    foreach ($form_themes as $form_theme) {
      $this->assertArrayHasKey(self::FORM_THEME_PREFIX . $form_theme, $result);

      $this->assertArrayHasKey('path', $result[self::FORM_THEME_PREFIX . $form_theme]);
      $this->assertEquals(self::FORM_TEMPLATE_PATH, $result[self::FORM_THEME_PREFIX . $form_theme]['path']);

      $this->assertArrayHasKey('render element', $result[self::FORM_THEME_PREFIX . $form_theme]);
      $this->assertEquals('form', $result[self::FORM_THEME_PREFIX . $form_theme]['render element']);

      $this->assertArrayHasKey('template', $result[self::FORM_THEME_PREFIX . $form_theme]);
      $kebab_case_form_theme = str_replace('_', '-', $form_theme);
      $this->assertEquals($kebab_case_form_theme, $result[self::FORM_THEME_PREFIX . $form_theme]['template']);
    }

    // Form-element themes.
    // 1. Expanded radio.
    $expandedRadioTheme = self::FORM_ELEMENT_THEME_PREFIX . 'expanded_radio';
    $this->assertArrayHasKey($expandedRadioTheme, $result);
    $this->assertArrayHasKey('base hook', $result[$expandedRadioTheme]);
    $this->assertEquals('radio', $result[$expandedRadioTheme]['base hook']);
    $this->assertArrayHasKey('path', $result[$expandedRadioTheme]);
    $this->assertEquals(self::FORM_ELEMENT_TEMPLATE_PATH, $result[$expandedRadioTheme]['path']);
    $this->assertArrayHasKey('template', $result[$expandedRadioTheme]);
    $this->assertEquals('expanded-radio', $result[$expandedRadioTheme]['template']);

    // Page-element themes.
    // 1. Expanded radio -- Help text with optional image.
    $expandedRadioHelpTextTheme = self::PAGE_ELEMENT_THEME_PREFIX . 'expanded_radio__help_text_optional_image';
    $this->assertArrayHasKey($expandedRadioHelpTextTheme, $result);
    $this->assertArrayHasKey('path', $result[$expandedRadioHelpTextTheme]);
    $this->assertEquals(self::PAGE_ELEMENT_TEMPLATE_PATH, $result[$expandedRadioHelpTextTheme]['path']);
    $this->assertArrayHasKey('template', $result[$expandedRadioHelpTextTheme]);
    $this->assertEquals('expanded-radio--help-text-optional-image', $result[$expandedRadioHelpTextTheme]['template']);
    $this->assertArrayHasKey('variables', $result[$expandedRadioHelpTextTheme]);
    $this->assertArrayHasKey('help_text', $result[$expandedRadioHelpTextTheme]['variables']);
    $this->assertArrayHasKey('image', $result[$expandedRadioHelpTextTheme]['variables']);
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
