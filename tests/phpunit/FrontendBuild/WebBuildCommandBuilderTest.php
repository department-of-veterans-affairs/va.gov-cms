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
      'va_gov_composer_home' => '/composer/home',
      'va_gov_path_to_composer' => '/composer/file/here',
      'va_gov_app_root' => '/app/root',
      'va_gov_web_root' => '/repo/root',
    ];

    $unique_key = 'abcssss';

    $settings = new Settings($settings_array);

    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->createMock('Drupal\Core\State\StateInterface');
    $webBuildStatus = new WebBuildStatus($state, $settings);
    $webBuildCommandBuilder = new WebBuildCommandBuilder($settings, $webBuildStatus);

    $commands = $webBuildCommandBuilder->buildCommands(NULL, $unique_key);
    self::assertEquals(
      'cd /repo/root && rm -fr docroot/vendor/va-gov',
      $commands[0],
      'Web Commands build with GraphQL'
    );
    self::assertEquals(
      'cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache install',
      $commands[1],
      'Web Commands build with GraphQL'
    );
    self::assertEquals(
      'cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build',
      $commands[2],
      'Web Commands build with GraphQL'
    );

    $commands = $webBuildCommandBuilder->buildCommands('1234', $unique_key);
    $web_branch = "build-1234-abcssss";
    self::assertEquals(
      "cd /repo/root && git reset --hard HEAD",
      $commands[0],
      'Web Command Build with commit and GraphQL git command'
    );

    self::assertEquals(
      "cd /repo/root && git fetch origin pull/1234/head:{$web_branch} && git checkout {$web_branch}",
      $commands[1],
      'Web Command Build with commit and GraphQL git command'
    );

    self::assertEquals(
      "cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:install",
      $commands[2],
      'Web Command Build with commit and GraphQL npm install command'
    );

    self::assertEquals(
      "cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:full",
      $commands[3],
      'Web Command Build with commit and GraphQL composer command'
    );

    $commands = $webBuildCommandBuilder->buildCommands('abcd', $unique_key);
    $web_branch = "build-abcd-abcssss";
    self::assertEquals(
      "cd /repo/root && git reset --hard HEAD",
      $commands[0],
      'Web command build with branch and GraphQL git command'
    );

    self::assertEquals(
      "cd /repo/root && git fetch origin && git checkout -b build-abcd-abcssss origin/abcd",
      $commands[1],
      'Web command build with branch and GraphQL git command'
    );

    self::assertEquals(
      "cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:install",
      $commands[2],
      'Web Command Build with branch and GraphQL npm install command'
    );

    self::assertEquals(
      "cd /app/root && COMPOSER_HOME=/composer/home /composer/file/here --no-cache va:web:build:full",
      $commands[3],
      'Web command build with branch and GraphQL composer command'
    );
  }

}
