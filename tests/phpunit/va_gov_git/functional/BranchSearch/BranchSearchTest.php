<?php

namespace tests\phpunit\va_gov_git\functional\BranchSearch\Factory;

use Drupal\va_gov_git\BranchSearch\BranchSearch;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Branch Search services.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\BranchSearch\BranchSearch
 */
class BranchSearchTest extends VaGovExistingSiteBase {

  /**
   * Test that each service is available.
   *
   * @param string $serviceName
   *   The service name.
   *
   * @dataProvider getDataProvider
   */
  public function testConstruct($serviceName) {
    $branchSearch = \Drupal::service($serviceName);
    $this->assertInstanceOf(BranchSearch::class, $branchSearch);
  }

  /**
   * Data provider for testConstruct().
   */
  public function getDataProvider() {
    return [
      ['va_gov_git.branch_search.va_gov_cms'],
      ['va_gov_git.branch_search.content_build'],
    ];
  }

  /**
   * Verify that a search returns results.
   *
   * @param string $serviceName
   *   The service name.
   * @param string $searchTerm
   *   The search term.
   *
   * @dataProvider getRemoteBranchNamesContainingDataProvider
   */
  public function testGetRemoteBranchNamesContaining($serviceName, $searchTerm) {
    $branchSearch = \Drupal::service($serviceName);
    $results = $branchSearch->getRemoteBranchNamesContaining($searchTerm);
    $this->assertNotEmpty($results);
    $this->assertContains($searchTerm, $results);
  }

  /**
   * Data provider for testGetRemoteBranchNamesContaining().
   *
   * @return array
   *   The data.
   */
  public function getRemoteBranchNamesContainingDataProvider() {
    return [
      ['va_gov_git.branch_search.va_gov_cms', 'main'],
      ['va_gov_git.branch_search.content_build', 'main'],
    ];
  }

}
