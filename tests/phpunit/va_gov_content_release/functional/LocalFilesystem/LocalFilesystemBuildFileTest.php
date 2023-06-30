<?php

namespace tests\phpunit\va_gov_content_release\functional\LocalFilesystem;

use Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFile;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the LocalFilesystemBuildFile service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFile
 */
class LocalFilesystemBuildFileTest extends VaGovExistingSiteBase {

  /**
   * Test that the Strategy Plugin Manager service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf(LocalFilesystemBuildFile::class, \Drupal::service('va_gov_content_release.local_filesystem_build_file'));
  }

}
