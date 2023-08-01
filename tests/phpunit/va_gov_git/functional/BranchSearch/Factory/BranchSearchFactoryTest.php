<?php

namespace tests\phpunit\va_gov_git\functional\BranchSearch\Factory;

use Drupal\va_gov_git\BranchSearch\Factory\BranchSearchFactory;
use Drupal\va_gov_git\BranchSearch\BranchSearchInterface;
use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Branch Search Factory service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\BranchSearch\Factory\BranchSearchFactory
 */
class BranchSearchFactoryTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $branchSearchFactory = \Drupal::service('va_gov_git.branch_search_factory');
    $this->assertInstanceOf(BranchSearchFactory::class, $branchSearchFactory);
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
    $branchSearch = \Drupal::service('va_gov_git.branch_search_factory')->get($repositoryName);
    $this->assertInstanceOf(BranchSearchInterface::class, $branchSearch);
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
    $branchSearch = \Drupal::service('va_gov_git.branch_search_factory')->getCms();
    $this->assertInstanceOf(BranchSearchInterface::class, $branchSearch);
  }

  /**
   * Test the getContentBuild() method.
   */
  public function testGetContentBuild() {
    $branchSearch = \Drupal::service('va_gov_git.branch_search_factory')->getContentBuild();
    $this->assertInstanceOf(BranchSearchInterface::class, $branchSearch);
  }

}
