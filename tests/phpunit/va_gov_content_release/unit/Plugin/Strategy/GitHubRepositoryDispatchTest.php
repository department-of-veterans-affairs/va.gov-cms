<?php

namespace tests\phpunit\va_gov_environment\unit\Plugin\Strategy;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_content_release\Exception\ContentReleaseInProgressException;
use Drupal\va_gov_content_release\Exception\GitHubRepositoryDispatchException;
use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\GitHub\GitHubRepositoryDispatchInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Drupal\va_gov_content_release\Plugin\Strategy\GitHubRepositoryDispatch;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the GitHub Repository Dispatch strategy plugin.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Plugin\Strategy\GitHubRepositoryDispatch
 */
class GitHubRepositoryDispatchTest extends VaGovUnitTestBase {

  /**
   * Construct a GitHubRepositoryDispatch strategy plugin.
   */
  public function getGitHubRepositoryDispatch() {
    $gitHubServiceProphecy = $this->prophesize(GitHubRepositoryDispatchInterface::class);
    $gitHubService = $gitHubServiceProphecy->reveal();
    $reporterProphecy = $this->prophesize(ReporterInterface::class);
    $reporter = $reporterProphecy->reveal();
    $stringTranslationProphecy = $this->prophesize(TranslationInterface::class);
    $stringTranslationProphecy->translateString(Argument::any())->will(function ($args) {
      return $args[0]->getUntranslatedString();
    });
    $stringTranslationService = $stringTranslationProphecy->reveal();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('va_gov_content_release.github_repository_dispatch')->willReturn($gitHubService);
    $containerProphecy->get('va_gov_content_release.reporter')->willReturn($reporter);
    $containerProphecy->get('string_translation')->willReturn($stringTranslationService);
    $container = $containerProphecy->reveal();
    return GitHubRepositoryDispatch::create($container, [], 'test', []);
  }

  /**
   * Test that the GitHub Repository Dispatch strategy plugin can be created.
   *
   * @covers ::create
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(GitHubRepositoryDispatch::class, $this->getGitHubRepositoryDispatch());
  }

  /**
   * Test that we can build a "submitted" message.
   *
   * @covers ::buildSubmittedMessage
   */
  public function testBuildSubmittedMessage() {
    $this->assertStringContainsString('The system started the process of releasing this content to go live on VA.gov.', $this->getGitHubRepositoryDispatch()->buildSubmittedMessage());
  }

  /**
   * Test that we can build an "already in progress" message.
   *
   * @covers ::buildAlreadyInProgressMessage
   */
  public function testBuildAlreadyInProgressMessage() {
    $this->assertStringContainsString('Changes will be included in a content release to VA.gov that\'s already in progress.', $this->getGitHubRepositoryDispatch()->buildAlreadyInProgressMessage());
  }

  /**
   * Test that we can build a "failure" message.
   *
   * @covers ::buildFailureMessage
   */
  public function testBuildFailureMessage() {
    $this->assertStringContainsString('A content release request has failed with an Exception.', $this->getGitHubRepositoryDispatch()->buildFailureMessage());
  }

  /**
   * Test that we can trigger a content release.
   *
   * Note this method should _never_ throw an exception other than
   * StrategyErrorException.
   *
   * @param \Throwable $exception
   *   The exception to throw, if any.
   * @param int $reportInfoCalls
   *   The number of times we expect an info message.
   * @param int $reportErrorCalls
   *   The number of times we expect an error message.
   *
   * @covers ::triggerContentRelease
   * @dataProvider triggerContentReleaseDataProvider
   */
  public function testTriggerContentRelease(\Throwable $exception = NULL, int $reportInfoCalls = 1, int $reportErrorCalls = 0) {
    $gitHubServiceProphecy = $this->prophesize(GitHubRepositoryDispatchInterface::class);
    $gitHubServiceProphecy->submit()->shouldBeCalledOnce();
    if ($exception) {
      $gitHubServiceProphecy->submit()->willThrow($exception);
    }
    $gitHubService = $gitHubServiceProphecy->reveal();
    $reporterProphecy = $this->prophesize(ReporterInterface::class);
    $reporterProphecy->reportInfo(Argument::type('string'))->shouldBeCalledTimes($reportInfoCalls);
    $reporterProphecy->reportError(Argument::type('string'), $exception)->shouldBeCalledTimes($reportErrorCalls);
    $reporter = $reporterProphecy->reveal();
    $stringTranslationProphecy = $this->prophesize(TranslationInterface::class);
    $stringTranslationProphecy->translateString(Argument::any())->will(function ($args) {
      return $args[0]->getUntranslatedString();
    });
    $stringTranslationService = $stringTranslationProphecy->reveal();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('va_gov_content_release.github_repository_dispatch')->willReturn($gitHubService);
    $containerProphecy->get('va_gov_content_release.reporter')->willReturn($reporter);
    $containerProphecy->get('string_translation')->willReturn($stringTranslationService);
    $container = $containerProphecy->reveal();
    $plugin = GitHubRepositoryDispatch::create($container, [], 'test', []);
    if ($reportErrorCalls > 0) {
      $this->expectException(StrategyErrorException::class);
    }
    $plugin->triggerContentRelease();
  }

  /**
   * Data provider for testTriggerContentRelease().
   *
   * @return array
   *   The data.
   */
  public function triggerContentReleaseDataProvider(): array {
    return [
      'no exception' => [
        'exception' => NULL,
        'reportInfoCalls' => 1,
        'reportErrorCalls' => 0,
      ],
      'ContentReleaseInProgressException' => [
        'exception' => new ContentReleaseInProgressException(),
        'reportInfoCalls' => 1,
        'reportErrorCalls' => 0,
      ],
      'GitHubRepositoryDispatchException' => [
        'exception' => new GitHubRepositoryDispatchException(),
        'reportInfoCalls' => 0,
        'reportErrorCalls' => 1,
      ],
      'other exception' => [
        'exception' => new \Exception(),
        'reportInfoCalls' => 0,
        'reportErrorCalls' => 1,
      ],
    ];
  }

}
