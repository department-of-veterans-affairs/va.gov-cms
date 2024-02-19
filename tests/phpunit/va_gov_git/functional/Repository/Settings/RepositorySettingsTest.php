<?php

namespace tests\phpunit\va_gov_git\functional\Repository\Settings;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_git\Repository\Settings\RepositorySettings;
use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Repository Settings service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\Repository\Settings\RepositorySettings
 */
class RepositorySettingsTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $repositorySettings = \Drupal::service('va_gov_git.repository_settings');
    $this->assertInstanceOf(RepositorySettings::class, $repositorySettings);
  }

  /**
   * Test that the service returns the correct names.
   *
   * @covers ::getNames
   */
  public function testGetNames() {
    $repositorySettings = \Drupal::service('va_gov_git.repository_settings');
    $this->assertEquals(RepositorySettingsInterface::REPOSITORY_NAMES, $repositorySettings->getNames());
  }

  /**
   * Test that the service returns the correct path key.
   *
   * @covers ::getPathKey
   */
  public function testGetPathKey() {
    $repositorySettings = \Drupal::service('va_gov_git.repository_settings');
    $this->assertEquals(RepositorySettingsInterface::PATH_KEYS['va.gov-cms'], $repositorySettings->getPathKey('va.gov-cms'));
    $this->assertEquals(RepositorySettingsInterface::PATH_KEYS['content-build'], $repositorySettings->getPathKey('content-build'));
    $this->assertEquals(RepositorySettingsInterface::PATH_KEYS['vets-website'], $repositorySettings->getPathKey('vets-website'));
    $this->assertEquals(RepositorySettingsInterface::PATH_KEYS['next-build'], $repositorySettings->getPathKey('next-build'));
  }

  /**
   * Test list().
   *
   * @covers ::list
   */
  public function testList() {
    $repositorySettings = \Drupal::service('va_gov_git.repository_settings');
    $this->assertEquals([
      [
        'name' => RepositorySettingsInterface::VA_GOV_CMS,
        'path' => Settings::get('va_gov_app_root'),
      ],
      [
        'name' => RepositorySettingsInterface::CONTENT_BUILD,
        'path' => Settings::get('va_gov_web_root'),
      ],
      [
        'name' => RepositorySettingsInterface::VETS_WEBSITE,
        'path' => Settings::get('va_gov_vets_website_root'),
      ],
      [
        'name' => RepositorySettingsInterface::NEXT_BUILD,
        'path' => Settings::get('va_gov_next_build_root'),
      ],
    ], $repositorySettings->list());
  }

}
