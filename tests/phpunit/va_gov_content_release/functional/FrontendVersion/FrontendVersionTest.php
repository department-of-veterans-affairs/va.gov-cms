<?php

namespace tests\phpunit\va_gov_content_release\functional\FrontendVersion;

use Drupal\va_gov_content_release\FrontendVersion\FrontendVersion;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Frontend Version service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\FrontendVersion\FrontendVersion
 */
class FrontendVersionTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $frontendVersion = \Drupal::service('va_gov_content_release.frontend_version');
    $this->assertInstanceOf(FrontendVersion::class, $frontendVersion);
  }

}
