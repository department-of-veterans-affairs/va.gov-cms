<?php

namespace tests\phpunit\va_gov_content_release\unit\Plugin\Strategy;

use Drupal\Core\File\Exception\FileException;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_content_release\Exception\StrategyErrorException;
use Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFileInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Drupal\va_gov_content_release\Plugin\Strategy\LocalFilesystemBuildFile;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the GitHub Repository Dispatch strategy plugin.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Plugin\Strategy\LocalFilesystemBuildFile
 */
class LocalFilesystemBuildFileTest extends VaGovUnitTestBase {

  /**
   * Construct a LocalFilesystemBuildFile strategy plugin.
   */
  public function getLocalFilesystemBuildFile() {
    $reporterProphecy = $this->prophesize(ReporterInterface::class);
    $reporter = $reporterProphecy->reveal();
    $stringTranslationProphecy = $this->prophesize(TranslationInterface::class);
    $stringTranslationProphecy->translateString(Argument::any())->will(function ($args) {
      return $args[0]->getUntranslatedString();
    });
    $stringTranslationService = $stringTranslationProphecy->reveal();
    $lfbfProphecy = $this->prophesize(LocalFilesystemBuildFileInterface::class);
    $lfbf = $lfbfProphecy->reveal();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('va_gov_content_release.reporter')->willReturn($reporter);
    $containerProphecy->get('string_translation')->willReturn($stringTranslationService);
    $containerProphecy->get('va_gov_content_release.local_filesystem_build_file')->willReturn($lfbf);
    $container = $containerProphecy->reveal();
    return LocalFilesystemBuildFile::create($container, [], 'test', []);
  }

  /**
   * Test that the LocalFilesystemBuildFile strategy plugin can be created.
   *
   * @covers ::create
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(LocalFilesystemBuildFile::class, $this->getLocalFilesystemBuildFile());
  }

  /**
   * Test that we can build a "submitted" message.
   *
   * @covers ::buildSubmittedMessage
   */
  public function testBuildSubmittedMessage() {
    $this->assertStringContainsString('A content release request has been submitted.', $this->getLocalFilesystemBuildFile()->buildSubmittedMessage());
  }

  /**
   * Test that we can build a "failure" message.
   *
   * @covers ::buildFailureMessage
   */
  public function testBuildFailureMessage() {
    $this->assertStringContainsString('A content release request has failed.', $this->getLocalFilesystemBuildFile()->buildFailureMessage());
  }

  /**
   * Test that we can submit a content release request.
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
    $reporterProphecy = $this->prophesize(ReporterInterface::class);
    $reporterProphecy->reportInfo(Argument::type('string'))->shouldBeCalledTimes($reportInfoCalls);
    $reporterProphecy->reportError(Argument::type('string'), $exception)->shouldBeCalledTimes($reportErrorCalls);
    $reporter = $reporterProphecy->reveal();
    $stringTranslationProphecy = $this->prophesize(TranslationInterface::class);
    $stringTranslationProphecy->translateString(Argument::any())->will(function ($args) {
      return $args[0]->getUntranslatedString();
    });
    $stringTranslationService = $stringTranslationProphecy->reveal();
    $lfbfProphecy = $this->prophesize(LocalFilesystemBuildFileInterface::class);
    if ($exception) {
      $lfbfProphecy->submit()->shouldBeCalledOnce()->willThrow($exception);
    }
    else {
      $lfbfProphecy->submit()->shouldBeCalledOnce();
    }
    $lfbf = $lfbfProphecy->reveal();
    $containerProphecy = $this->prophesize(ContainerInterface::class);
    $containerProphecy->get('va_gov_content_release.reporter')->willReturn($reporter);
    $containerProphecy->get('string_translation')->willReturn($stringTranslationService);
    $containerProphecy->get('va_gov_content_release.local_filesystem_build_file')->willReturn($lfbf);
    $container = $containerProphecy->reveal();
    $plugin = LocalFilesystemBuildFile::create($container, [], 'test', []);
    if ($exception) {
      $this->expectException(StrategyErrorException::class);
    }
    $plugin->triggerContentRelease();
  }

  /**
   * Data provider for testTriggerContentRelease().
   *
   * @return array
   *   The test data.
   */
  public function triggerContentReleaseDataProvider() {
    return [
      'no exception' => [
        'exception' => NULL,
        'reportInfoCalls' => 1,
        'reportErrorCalls' => 0,
      ],
      'StrategyErrorException' => [
        'exception' => new StrategyErrorException(),
        'reportInfoCalls' => 0,
        'reportErrorCalls' => 1,
      ],
      'FileException' => [
        'exception' => new FileException(),
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
