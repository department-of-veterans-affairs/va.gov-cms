<?php

namespace tests\phpunit\va_gov_content_release\functional\GitHub;

use Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatch;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the GitHubRepositoryDispatch service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatch
 */
class GitHubRepositoryDispatchTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(GitHubRepositoryDispatch::class, \Drupal::service('va_gov_content_release.github_repository_dispatch'));
  }

}
