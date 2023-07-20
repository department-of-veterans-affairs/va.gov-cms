<?php

namespace tests\phpunit\va_gov_git\functional\Repository;

use Drupal\va_gov_git\Repository\RepositoryInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Repository class.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\Repository\Repository
 */
class RepositoryTest extends VaGovExistingSiteBase {

  /**
   * Test that we can instantiate the CMS repository.
   */
  public function testConstructCms() {
    $repository = \Drupal::service('va_gov_git.repository_factory')->getCms();
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
    $repository = \Drupal::service('va_gov_git.repository.va_gov_cms');
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
  }

  /**
   * Test that we can instantiate the Content Build repository.
   */
  public function testConstructContentBuild() {
    $repository = \Drupal::service('va_gov_git.repository_factory')->getContentBuild();
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
    $repository = \Drupal::service('va_gov_git.repository.content_build');
    $this->assertInstanceOf(RepositoryInterface::class, $repository);
  }

  /**
   * Test that each repository has remote branches.
   *
   * @param string $repositoryName
   *   The repository name.
   * @param string $branchName
   *   The branch name.
   * @param bool $expected
   *   Whether or not the branch is expected to exist.
   *
   * @covers ::getRemoteBranchNames
   * @dataProvider getRemoteBranchNamesDataProvider
   */
  public function testGetRemoteBranchNames(string $repositoryName, string $branchName, bool $expected) {
    $repository = \Drupal::service('va_gov_git.repository_factory')->get($repositoryName);
    $remoteBranchNames = $repository->getRemoteBranchNames();
    if ($expected) {
      $this->assertContains($branchName, $remoteBranchNames, "Branch $branchName is expected to exist.");
    }
    else {
      $this->assertNotContains($branchName, $remoteBranchNames, "Branch $branchName is not expected to exist.");
    }
  }

  /**
   * Data provider for testGetRemoteBranchNames().
   *
   * @return array
   *   The data.
   */
  public function getRemoteBranchNamesDataProvider() {
    return [
      [
        'va.gov-cms',
        'refs/remotes/origin/main',
        TRUE,
      ],
      [
        'va.gov-cms',
        'refs/remotes/origin/this-branch-does-not-exist',
        FALSE,
      ],
      [
        'content-build',
        'refs/remotes/origin/main',
        TRUE,
      ],
      [
        'content-build',
        'refs/remotes/origin/this-branch-does-not-exist',
        FALSE,
      ],
    ];
  }

}
