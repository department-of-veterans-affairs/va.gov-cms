<?php

namespace tests\phpunit\va_gov_form_builder\unit;

use DrupalFinder\DrupalFinder;
use Symfony\Component\Yaml\Yaml;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for va_gov_form_builder.libraries.yml.
 *
 * @group unit
 * @group all
 */
class LibrariesTest extends VaGovUnitTestBase {

  /**
   * The path to the module being tested.
   *
   * @var string
   */
  private $modulePath = 'modules/custom/va_gov_form_builder';

  /**
   * The libraries defined in libraries.yml.
   *
   * @var array<string, array{
   *   css?: array{
   *     theme?: array<string, mixed>
   *   },
   *   js?: array<string, mixed>,
   *   dependencies?: array<string>
   * }>
   */
  private $libraries;

  /**
   * Set up the environment for each test.
   */
  protected function setUp(): void {
    parent::setUp();

    // Find Drupal root.
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(__DIR__);
    $drupalRoot = $drupalFinder->getDrupalRoot();

    $yamlFile = $drupalRoot . '/' . $this->modulePath . '/va_gov_form_builder.libraries.yml';

    $this->libraries = Yaml::parseFile($yamlFile);
  }

  /**
   * Tests that the library definition contains necessary css.
   */
  public function testLibraryCss() {
    $cssPrefix = 'css/';

    // Assert Form Builder base library is present.
    $baseLibrary = 'form_builder';
    $this->assertArrayHasKey($baseLibrary, $this->libraries);
    $this->assertArrayHasKey('css', $this->libraries[$baseLibrary]);
    $this->assertArrayHasKey('theme', $this->libraries[$baseLibrary]['css']);
    // Assert Form Builder css is present.
    $formBuilderCssArray = array_keys($this->libraries[$baseLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'form-builder.css', $formBuilderCssArray, 'Form Builder css is present.');
    // Assert external VADS tokens definition is present.
    $matches = preg_grep('/unpkg.*@department-of-veterans-affairs.*css-library.*tokens\/css\/variables.css/', $formBuilderCssArray);
    $this->assertNotEmpty($matches, 'VADS tokens css is present.');

    // Assert page-specific libraries are present.
    // 1. Home.
    $homeLibrary = 'home';
    $this->assertArrayHasKey($homeLibrary, $this->libraries);
    $homeCssArray = array_keys($this->libraries[$homeLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'home.css', $homeCssArray, 'Home page css is present.');
    // 2. Form info.
    $formInfoLibrary = 'form_info';
    $this->assertArrayHasKey($formInfoLibrary, $this->libraries);
    $formInfoCssArray = array_keys($this->libraries[$formInfoLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'form-info.css', $formInfoCssArray, 'Form Info page css is present.');
    // 3. Layout.
    $layoutLibrary = 'layout';
    $this->assertArrayHasKey($layoutLibrary, $this->libraries);
    $layoutCssArray = array_keys($this->libraries[$layoutLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'layout.css', $layoutCssArray, 'Layout page css is present.');
    // 4. Single column with buttons.
    $singleColumnWithButtonsLibrary = 'single_column_with_buttons';
    $this->assertArrayHasKey($singleColumnWithButtonsLibrary, $this->libraries);
    $singleColumnWithButtonsCssArray = array_keys($this->libraries[$singleColumnWithButtonsLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'single-column-with-buttons.css', $singleColumnWithButtonsCssArray, 'Non-editable-pattern css is present.');
    // 5. Non-editable pattern.
    $nonEditablePatternLibrary = 'non_editable_pattern';
    $this->assertArrayHasKey($nonEditablePatternLibrary, $this->libraries);
    $nonEditablePatternCssArray = array_keys($this->libraries[$nonEditablePatternLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'non-editable-pattern.css', $nonEditablePatternCssArray, 'Non-editable-pattern css is present.');
    // 6. Step label.
    $stepLabelPatternLibrary = 'step_label';
    $this->assertArrayHasKey($stepLabelPatternLibrary, $this->libraries);
    $stepLabelCssArray = array_keys($this->libraries[$stepLabelPatternLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'step-label.css', $stepLabelCssArray, 'Step-label css is present.');
    // 6. Step style.
    $stepStylePatternLibrary = 'step_style';
    $this->assertArrayHasKey($stepStylePatternLibrary, $this->libraries);
    $stepStyleCssArray = array_keys($this->libraries[$stepStylePatternLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . 'step-style.css', $stepStyleCssArray, 'Step-style css is present.');
  }

}
