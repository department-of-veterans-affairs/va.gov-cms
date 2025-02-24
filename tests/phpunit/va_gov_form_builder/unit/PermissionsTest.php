<?php

namespace tests\phpunit\va_gov_form_builder\unit;

use DrupalFinder\DrupalFinder;
use Symfony\Component\Yaml\Yaml;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for va_gov_form_builder.permissions.yml.
 *
 * @group unit
 * @group all
 */
class PermissionsTest extends VaGovUnitTestBase {

  /**
   * The path to the module being tested.
   *
   * @var string
   */
  private $modulePath = 'modules/custom/va_gov_form_builder';

  /**
   * The permissions defined in permissions.yml.
   *
   * @var array<string, array{
   *   title: string,
   *   description?: string,
   *   restrict access?: bool,
   * }>
   */
  private $permissions;

  /**
   * Set up the environment for each test.
   */
  protected function setUp(): void {
    parent::setUp();

    // Find Drupal root.
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(__DIR__);
    $drupalRoot = $drupalFinder->getDrupalRoot();

    $yamlFile = $drupalRoot . '/' . $this->modulePath . '/va_gov_form_builder.permissions.yml';

    $this->permissions = Yaml::parseFile($yamlFile);
  }

  /**
   * Tests that the `access form builder` permission is present.
   */
  public function testPermissionPresent() {
    $this->assertArrayHasKey('access form builder', $this->permissions);

    // Assert expected keys are present.
    $formBuilderPermission = $this->permissions['access form builder'];
    $this->assertArrayHasKey('title', $formBuilderPermission);
    $this->assertArrayHasKey('description', $formBuilderPermission);
    $this->assertArrayHasKey('restrict access', $formBuilderPermission);

    // Assert `restrict access` is false.
    $restrictAccess = $formBuilderPermission['restrict access'];
    $this->assertFalse($restrictAccess);
  }

}
