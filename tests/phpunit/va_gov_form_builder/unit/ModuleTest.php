<?php

namespace tests\phpunit\va_gov_form_builder\unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ModuleExtensionList;
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

    // Mock the extension.list.module service.
    $extensionListMock = $this->createMock(ModuleExtensionList::class);
    $extensionListMock->expects($this->once())
      ->method('getPath')
      ->with('va_gov_form_builder')
      ->willReturn($this->modulePath);

    // Create a mock container.
    $container = new ContainerBuilder();
    $container->set('extension.list.module', $extensionListMock);

    // Set the mocked container as the global Drupal container.
    \Drupal::setContainer($container);
  }

  /**
   * Tests va_gov_form_builder_theme().
   *
   * @covers ::va_gov_form_builder_theme
   */
  public function testVaGovFormBuilderHookTheme() {
    // Call the function to test.
    $result = va_gov_form_builder_theme();

    // Assert the expected theme definition exists.
    $this->assertArrayHasKey('va_gov_form_builder_page', $result);
    $this->assertEquals('page--va-gov-form-builder', $result['va_gov_form_builder_page']['template']);
    $this->assertEquals($this->modulePath . '/templates', $result['va_gov_form_builder_page']['path']);
  }

}
