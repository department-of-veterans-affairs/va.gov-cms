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
    $libraryPrefix = 'va_gov_form_builder_styles';
    $cssPrefix = 'css/va_gov_form_builder';

    $this->assertArrayHasKey($libraryPrefix, $this->libraries);
    $this->assertArrayHasKey('css', $this->libraries[$libraryPrefix]);
    $this->assertArrayHasKey('theme', $this->libraries[$libraryPrefix]['css']);

    $formBuilderCssArray = array_keys($this->libraries[$libraryPrefix]['css']['theme']);

    // Assert Form Builder css is present.
    $this->assertContains($cssPrefix . '.css', $formBuilderCssArray, 'Form Builder css is present.');

    // Assert external VADS tokens definition is present.
    $matches = preg_grep('/unpkg.*@department-of-veterans-affairs.*css-library.*tokens\/css\/variables.css/', $formBuilderCssArray);
    $this->assertNotEmpty($matches, 'VADS tokens css is present.');

    // Assert page-specific libraries are present
    // 1. Home.
    $homeLibrary = $libraryPrefix . '__home';
    $this->assertArrayHasKey($homeLibrary, $this->libraries);
    $homeCssArray = array_keys($this->libraries[$homeLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . '__home.css', $homeCssArray, 'Home page css is present.');
    // 2. Form Info.
    $formInfoLibrary = $libraryPrefix . '__form_info';
    $this->assertArrayHasKey($formInfoLibrary, $this->libraries);
    $formInfoCssArray = array_keys($this->libraries[$formInfoLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . '__form_info.css', $formInfoCssArray, 'Form Info page css is present.');
    // 3. Layout.
    $layoutLibrary = $libraryPrefix . '__layout';
    $this->assertArrayHasKey($layoutLibrary, $this->libraries);
    $layoutCssArray = array_keys($this->libraries[$layoutLibrary]['css']['theme']);
    $this->assertContains($cssPrefix . '__layout.css', $layoutCssArray, 'Layout page css is present.');
  }

}
