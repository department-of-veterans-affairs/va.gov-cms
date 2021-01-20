<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilder;
use Drupal\va_gov_build_trigger\WebBuildStatus;

/**
 * @covers \Drupal\va_gov_build_trigger\WebBuildCommandBuilder
 */
class WebBuildCommandBuilderTest extends UnitTestCase {

  /**
   * Test the building of commands.
   *
   * @covers \Drupal\va_gov_build_trigger\WebBuildCommandBuilder::buildCommands
   */
  public function testBuildCommandsGraphQl() {
    $settings_array = [
      'va_gov_use_cms_export' => FALSE,
      'va_gov_composer_home' => '/composer/home',
      'va_gov_path_to_composer' => '/composer/file/here',
    ];

    $unique_key = 'abcssss';

    $settings = new Settings($settings_array);
    $app_root = '/app/root';

    $webBuildCommandBuilder = new WebBuildCommandBuilder($app_root, $settings);

    $commands = $webBuildCommandBuilder->buildCommands('/repo/root', NULL, $unique_key);
    self::assertEquals(
      'cd /repo/root && && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build',
      $commands[0],
      'Web Commands build with GraphQL'
    );

    $commands = $webBuildCommandBuilder->buildCommands('/repo/root', '1234', $unique_key);
    $web_branch = "build-1234-222222-abcssss";
    self::assertEquals(
      "cd /repo/root/web && git fetch origin pull/1234/head:{$web_branch} && git checkout {$web_branch}",
      $commands[0],
      'Web Command Build with commit and GraphQL git command'
    );

    self::assertEquals(
      "cd /repo/root && && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:full",
      $commands[1],
      'Web Command Build with commit and GraphQL composer command'
    );

    $commands = $webBuildCommandBuilder->buildCommands('/repo/root', 'abcd', $unique_key);
    $web_branch = "build-abcd-222222-abcssss";
    self::assertEquals(
      "cd /repo/root/web && git checkout -b {$web_branch} origin/abcd",
      $commands[0],
      'Web command build with branch and GraphQL git command'
    );

    self::assertEquals(
      "cd /repo/root && && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:full",
      $commands[1],
      'Web command build with branch and composer command'
    );
  }

  /**
   * Test the building of commands.
   *
   * @covers \Drupal\va_gov_build_trigger\WebBuildCommandBuilder::buildCommands
   */
  public function testBuildCommandsContentExport() {

    $settings_array = [
      'va_gov_use_cms_export' => TRUE,
      'va_gov_composer_home' => '/composer/home',
      'va_gov_path_to_composer' => '/composer/file/here',
    ];

    $unique_key = 'abcssss';
    $settings = new Settings($settings_array);
    $app_root = '/app/root';
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->createMock('Drupal\Core\State\StateInterface');
    $webBuildStatus = new WebBuildStatus($state, $settings);
    $webBuildCommandBuilder = new WebBuildCommandBuilder($app_root, $settings, $webBuildStatus);

    $commands = $webBuildCommandBuilder->buildCommands('/repo/root', NULL, $unique_key);
    self::assertEquals(
      'cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:export',
      $commands[0],
      'Web Commands build with CMS Export'
    );

    $commands = $webBuildCommandBuilder->buildCommands('/repo/root', '1234', $unique_key);
    $web_branch = "build-1234-abcssss";
    self::assertEquals(
      "cd /repo/root && git fetch origin pull/1234/head:{$web_branch} && git checkout {$web_branch}",
      $commands[0],
      'Web Command Build with commit and CMS Export git command'
    );

    self::assertEquals(
      "cd /app/root && && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:export:full",
      $commands[1],
      'Web Command Build with commit and CMS Export composer command'
    );

    $commands = $webBuildCommandBuilder->buildCommands('/repo/root', 'abcd', $unique_key);
    $web_branch = "build-abcd-222222-abcssss";
    self::assertEquals(
      "cd /repo/root && git checkout -b {$web_branch} origin/abcd",
      $commands[0],
      'Web command build with branch and CMS Export git command'
    );

    self::assertEquals(
      "cd /app/root && && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:export:full",
      $commands[1],
      'Web command build with branch and composer command'
    );
  }

  /**
   * Test the useContentExport setting.
   *
   * @covers \Drupal\va_gov_build_trigger\WebBuildCommandBuilder::useContentExport
   */
  public function testUseContentExport() {
    $settings_array = [
      'va_gov_use_cms_export' => FALSE,
      'va_gov_composer_home' => '/composer/home',
      'va_gov_path_to_composer' => '/composer/file/here',
    ];

    $settings = new Settings($settings_array);
    $app_root = '/app/root';

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->createMock('Drupal\Core\State\StateInterface');
    $webBuildStatus = new WebBuildStatus($state, $settings);
    $webBuildCommandBuilder = new WebBuildCommandBuilder($app_root, $settings, $webBuildStatus);

    self::assertFalse(
      $webBuildCommandBuilder->useContentExport(),
      'Use content export set correctly'
    );

    $settings_array = [
      'va_gov_use_cms_export' => TRUE,
      'va_gov_composer_home' => '/composer/home',
      'va_gov_path_to_composer' => '/composer/file/here',
    ];

    $settings = new Settings($settings_array);
    $app_root = '/app/root';
    $webBuildStatus = new WebBuildStatus($state, $settings);
    $webBuildCommandBuilder = new WebBuildCommandBuilder($app_root, $settings, $webBuildStatus);

    self::assertTrue(
      $webBuildCommandBuilder->useContentExport(),
      'Use content export set correctly'
    );
  }

}
