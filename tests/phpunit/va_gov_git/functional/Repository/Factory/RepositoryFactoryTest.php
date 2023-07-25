<?php

namespace tests\phpunit\va_gov_git\functional\Repository\Factory;

use Drupal\va_gov_git\Repository\Factory\RepositoryFactory;
use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;
use Drupal\va_gov_git\Repository\RepositoryInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Repository Factory service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\Repository\Factory\RepositoryFactory
 */
class RepositoryFactoryTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $repositoryFactory = \Drupal::service('va_gov_git.repository_factory');
    $this->assertInstanceOf(RepositoryFactory::class, $repositoryFactory);
  }

  /**
   * Test the get() method.
   *
   * @param string $repositoryName
   *   The repository name.
   *
   * @dataProvider getDataProvider
   */
  public function testGet(string $repositoryName) {
    $repository = \Drupal::service('va_gov_git.repository_factory')->get($repositoryName);
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
  }

  /**
   * Data provider for testGet().
   *
   * @return array
   *   The data.
   */
  public function getDataProvider() {
    return [
      [RepositorySettingsInterface::VA_GOV_CMS],
      [RepositorySettingsInterface::CONTENT_BUILD],
    ];
  }

  /**
   * Test the getCms() method.
   */
  public function testGetCms() {
    $repository = \Drupal::service('va_gov_git.repository_factory')->getCms();
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
  }

  /**
   * Test the getContentBuild() method.
   */
  public function testGetContentBuild() {
    $repository = \Drupal::service('va_gov_git.repository_factory')->getContentBuild();
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
  }

}
