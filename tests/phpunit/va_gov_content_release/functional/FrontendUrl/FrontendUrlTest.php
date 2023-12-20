<?php

namespace tests\phpunit\va_gov_content_release\functional\FrontendUrl;

use Drupal\va_gov_content_release\FrontendUrl\FrontendUrl;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Frontend URL service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\FrontendUrl\FrontendUrl
 */
class FrontendUrlTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $frontendUrl = \Drupal::service('va_gov_content_release.frontend_url');
    $this->assertInstanceOf(FrontendUrl::class, $frontendUrl);
    $this->assertNotEmpty($frontendUrl->getBaseUrl());
  }

}
