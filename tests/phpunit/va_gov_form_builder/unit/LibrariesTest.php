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
    $this->assertArrayHasKey('va_gov_form_builder_styles', $this->libraries);
    $this->assertArrayHasKey('css', $this->libraries['va_gov_form_builder_styles']);
    $this->assertArrayHasKey('theme', $this->libraries['va_gov_form_builder_styles']['css']);

    $cssArray = array_keys($this->libraries['va_gov_form_builder_styles']['css']['theme']);

    // Assert Form Builder css is present.
    $this->assertContains('css/va_gov_form_builder.css', $cssArray, 'Form Builder css is present.');

    // Assert external VADS tokens definition is present.
    $matches = preg_grep('/unpkg.*@department-of-veterans-affairs.*css-library.*tokens\/css\/variables.css/', $cssArray);
    $this->assertNotEmpty($matches, 'VADS tokens css is present.');
  }

}
