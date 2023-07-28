<?php

namespace tests\phpunit\va_gov_content_release\unit\GitHub;

use Drupal\va_gov_consumers\GitHub\GitHubClientInterface;
use Drupal\va_gov_content_release\Exception\ContentReleaseInProgressException;
use Drupal\va_gov_content_release\Exception\GitHubRepositoryDispatchException;
use Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatch;
use Prophecy\Argument;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the GitHub Repository Dispatch service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatch
 */
class GitHubRepositoryDispatchTest extends VaGovUnitTestBase {

  /**
   * Construct a GitHubRepositoryDispatch object.
   *
   * @return \Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatch
   *   The object.
   */
  public function getGitHubRepositoryDispatch() {
    $gitHubClientProphecy = $this->prophesize(GitHubClientInterface::class);
    $gitHubClient = $gitHubClientProphecy->reveal();
    return new GitHubRepositoryDispatch($gitHubClient);
  }

  /**
   * Test that the GitHub Repository Dispatch service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(GitHubRepositoryDispatch::class, $this->getGitHubRepositoryDispatch());
  }

  /**
   * Test that we calculate the correct pending workflow parameters.
   *
   * @covers ::buildPendingWorkflowParams
   */
  public function testBuildPendingWorkflowParams() {
    $expected = [
      'status' => 'pending',
      'created' => '>=2021-01-01T12:00:00+11:00',
    ];
    $this->assertEquals($expected, $this->getGitHubRepositoryDispatch()->buildPendingWorkflowParams(1609470000));
  }

  /**
   * Test that we can determine if a workflow is pending.
   *
   * @param array $workflowRuns
   *   The workflow runs.
   * @param bool $expected
   *   The expected result.
   * @param string $expectedException
   *   The expected exception, if any.
   * @param \Throwable $throwException
   *   The expected exception, if any.
   *
   * @covers ::isPending
   * @dataProvider isPendingDataProvider
   */
  public function testIsPending($workflowRuns, bool $expected, string $expectedException = NULL, \Throwable $throwException = NULL) {
    $gitHubClientProphecy = $this->prophesize(GitHubClientInterface::class);
    if ($throwException) {
      $gitHubClientProphecy->listWorkflowRuns('content-release.yml', Argument::any())->willThrow($throwException);
    }
    else {
      $gitHubClientProphecy->listWorkflowRuns('content-release.yml', Argument::any())->willReturn($workflowRuns);
    }
    if ($expectedException) {
      $this->expectException($expectedException);
    }
    $gitHubClient = $gitHubClientProphecy->reveal();
    $gitHubRepositoryDispatch = new GitHubRepositoryDispatch($gitHubClient);
    $this->assertEquals($expected, $gitHubRepositoryDispatch->isPending());
  }

  /**
   * Data provider for testIsPending.
   *
   * @return array
   *   The data.
   */
  public function isPendingDataProvider(): array {
    return [
      'no workflow runs' => [
        'workflowRuns' => [],
        'expected' => FALSE,
      ],
      'no pending workflow runs' => [
        'workflowRuns' => [
          'total_count' => 0,
        ],
        'expected' => FALSE,
      ],
      'pending workflow runs' => [
        'workflowRuns' => [
          'total_count' => 1,
        ],
        'expected' => TRUE,
      ],
      'pending workflow runs with exception' => [
        'workflowRuns' => [
          'total_count' => 1,
        ],
        'expected' => TRUE,
        'expectedException' => GitHubRepositoryDispatchException::class,
        'throwException' => new \Exception(),
      ],
    ];
  }

  /**
   * Test that we can dispatch a workflow.
   *
   * @param array $workflowRuns
   *   The workflow runs.
   * @param int $expectedDispatchCount
   *   The expected number of dispatches.
   * @param string $expectedException
   *   The expected exception, if any.
   * @param \Throwable $throwException
   *   The exception to throw, if any.
   *
   * @covers ::submit
   * @covers ::isPending
   * @dataProvider dispatchDataProvider
   */
  public function testDispatch($workflowRuns, int $expectedDispatchCount, string $expectedException = NULL, \Throwable $throwException = NULL) {
    $gitHubClientProphecy = $this->prophesize(GitHubClientInterface::class);
    if (!$throwException) {
      $gitHubClientProphecy->listWorkflowRuns('content-release.yml', Argument::any())->willReturn($workflowRuns);
    }
    else {
      $gitHubClientProphecy->listWorkflowRuns('content-release.yml', Argument::any())->willThrow($throwException);
    }
    $gitHubClientProphecy->repositoryDispatchWorkflow('content-release')->shouldBeCalledTimes($expectedDispatchCount);
    if ($expectedException) {
      $this->expectException($expectedException);
    }
    $gitHubClient = $gitHubClientProphecy->reveal();
    $gitHubRepositoryDispatch = new GitHubRepositoryDispatch($gitHubClient);
    $gitHubRepositoryDispatch->submit();
  }

  /**
   * Data provider for testDispatch.
   *
   * @return array
   *   The data.
   */
  public function dispatchDataProvider() {
    return [
      'no pending workflows' => [
        'workflowRuns' => [
          'total_count' => 0,
        ],
        'expectedDispatchCount' => 1,
      ],
      'pending workflows' => [
        'workflowRuns' => [
          'total_count' => 1,
        ],
        'expectedDispatchCount' => 0,
        'expectedException' => ContentReleaseInProgressException::class,
      ],
      'unexpected_failure' => [
        'workflowRuns' => [
          'total_count' => 1,
        ],
        'expectedDispatchCount' => 0,
        'expectedException' => GitHubRepositoryDispatchException::class,
        'throwException' => new \Exception('Unexpected failure.'),
      ],
    ];
  }

}
