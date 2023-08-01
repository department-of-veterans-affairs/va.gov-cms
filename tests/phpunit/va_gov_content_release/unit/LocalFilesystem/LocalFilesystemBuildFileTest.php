<?php

namespace tests\phpunit\va_gov_content_release\unit\LocalFilesystem;

use Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFile;
use Tests\Support\Classes\VaGovUnitTestBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\va_gov_content_release\Exception\StrategyErrorException;

/**
 * Unit test of the LocalFilesystemBuildFile service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFile
 */
class LocalFilesystemBuildFileTest extends VaGovUnitTestBase {

  /**
   * Test the submit() function.
   *
   * @covers ::__construct
   * @covers ::submit
   */
  public function testSubmit() : void {
    $filesystemProphecy = $this->prophesize(FileSystemInterface::class);
    $filesystemProphecy
      ->saveData(LocalFilesystemBuildFile::FILE_CONTENTS, LocalFilesystemBuildFile::FILE_URI, FileSystemInterface::EXISTS_REPLACE)
      ->shouldBeCalledOnce();
    $filesystem = $filesystemProphecy->reveal();
    $localFilesystemBuildFile = new LocalFilesystemBuildFile($filesystem);
    $localFilesystemBuildFile->submit();
  }

  /**
   * Test the submit() function with a FileException.
   *
   * @covers ::__construct
   * @covers ::submit
   */
  public function testSubmitException(): void {
    $filesystemProphecy = $this->prophesize(FileSystemInterface::class);
    $filesystemProphecy
      ->saveData(LocalFilesystemBuildFile::FILE_CONTENTS, LocalFilesystemBuildFile::FILE_URI, FileSystemInterface::EXISTS_REPLACE)
      ->willThrow(new FileException('your filesystem is messed up'));
    $filesystem = $filesystemProphecy->reveal();
    $localFilesystemBuildFile = new LocalFilesystemBuildFile($filesystem);
    $this->expectException(StrategyErrorException::class);
    $localFilesystemBuildFile->submit();
  }

}
