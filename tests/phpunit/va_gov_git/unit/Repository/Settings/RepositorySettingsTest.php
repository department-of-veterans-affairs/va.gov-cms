<?php

namespace tests\phpunit\va_gov_git\unit\Repository\Settings;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_git\Exception\RepositoryPathNotSetException;
use Drupal\va_gov_git\Exception\UnknownRepositoryException;
use Drupal\va_gov_git\Repository\Settings\RepositorySettings;
use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Repository Settings service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\Repository\Settings\RepositorySettings
 */
class RepositorySettingsTest extends VaGovUnitTestBase {

  /**
   * Construct a testable RepositorySettings object.
   *
   * @return \Drupal\va_gov_git\Repository\Settings\RepositorySettings
   *   The testable RepositorySettings object.
   */
  public function getRepositorySettings() {
    $settings = new Settings([
      'va_gov_app_root' => '/srv/cms',
      'va_gov_web_root' => '/srv/web',
      'va_gov_vets_website_root' => '/srv/vets-website',
      'va_gov_next_build_root' => '/srv/next',
    ]);
    return new RepositorySettings($settings);
  }

  /**
   * Test getNames().
   */
  public function testGetNames() {
    $repositorySettings = $this->getRepositorySettings();
    $this->assertEquals(RepositorySettings::REPOSITORY_NAMES, $repositorySettings->getNames());
  }

  /**
   * Test getPathKey().
   *
   * @param string $repositoryName
   *   The repository name.
   * @param string $expectedPathKey
   *   The expected path key.
   *
   * @dataProvider getPathKeyDataProvider
   */
  public function testGetPathKey(string $repositoryName, string $expectedPathKey) {
    $repositorySettings = $this->getRepositorySettings();
    $this->assertEquals($expectedPathKey, $repositorySettings->getPathKey($repositoryName));
  }

  /**
   * Data provider for testGetPathKey().
   *
   * @return array
   *   The data.
   */
  public function getPathKeyDataProvider() {
    return [
      ['va.gov-cms', RepositorySettings::VA_GOV_CMS_PATH_KEY],
      ['content-build', RepositorySettings::CONTENT_BUILD_PATH_KEY],
      ['vets-website', RepositorySettings::VETS_WEBSITE_PATH_KEY],
      ['next-build', RepositorySettings::NEXT_BUILD_PATH_KEY],
    ];
  }

  /**
   * Test getPath().
   *
   * @param string $repositoryName
   *   The repository name.
   * @param string $expectedPath
   *   The expected path.
   *
   * @dataProvider getPathDataProvider
   */
  public function testGetPath(string $repositoryName, string $expectedPath) {
    $repositorySettings = $this->getRepositorySettings();
    $this->assertEquals($expectedPath, $repositorySettings->getPath($repositoryName));
  }

  /**
   * Data provider for testGetPath().
   *
   * @return array
   *   The data.
   */
  public function getPathDataProvider() {
    return [
      ['va.gov-cms', '/srv/cms'],
      ['content-build', '/srv/web'],
      ['vets-website', '/srv/vets-website'],
      ['next-build', '/srv/next'],
    ];
  }

  /**
   * Test getPathKey() with an invalid repository name.
   */
  public function testGetPathKeyInvalidRepositoryName() {
    $repositorySettings = $this->getRepositorySettings();
    $this->expectException(UnknownRepositoryException::class);
    $repositorySettings->getPathKey('invalid');
  }

  /**
   * Test getPath() with an invalid repository name.
   */
  public function testGetPathInvalidRepositoryName() {
    $repositorySettings = $this->getRepositorySettings();
    $this->expectException(UnknownRepositoryException::class);
    $repositorySettings->getPath('invalid');
  }

  /**
   * Test getPath() with a repository name that has no path set.
   */
  public function testGetPathRepositoryNameNoPathSet() {
    $settings = new Settings([]);
    $repositorySettings = new RepositorySettings($settings);
    $this->expectException(RepositoryPathNotSetException::class);
    $repositorySettings->getPath('va.gov-cms');
  }

  /**
   * Test list().
   */
  public function testList() {
    $repositorySettings = $this->getRepositorySettings();
    $this->assertEquals([
      [
        'name' => RepositorySettingsInterface::VA_GOV_CMS,
        'path' => '/srv/cms',
      ],
      [
        'name' => RepositorySettingsInterface::CONTENT_BUILD,
        'path' => '/srv/web',
      ],
      [
        'name' => RepositorySettingsInterface::VETS_WEBSITE,
        'path' => '/srv/vets-website',
      ],
      [
        'name' => RepositorySettingsInterface::NEXT_BUILD,
        'path' => '/srv/next',
      ],
    ], $repositorySettings->list());
  }

}
