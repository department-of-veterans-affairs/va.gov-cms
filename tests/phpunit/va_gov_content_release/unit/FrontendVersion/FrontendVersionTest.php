<?php

namespace tests\phpunit\va_gov_content_release\unit\FrontendVersion;

use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\va_gov_content_release\Frontend\Frontend;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersion;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Frontend Version service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\FrontendVersion\FrontendVersion
 */
class FrontendVersionTest extends VaGovUnitTestBase {

  /**
   * Test that the service works as expected.
   *
   * @covers ::__construct
   * @covers ::getVersion
   * @covers ::setVersion
   * @covers ::resetVersion
   */
  public function testGetSetReset() : void {
    $state = new State(new KeyValueMemoryFactory());
    $frontend = Frontend::ContentBuild;
    $frontendVersion = new FrontendVersion($state);
    $this->assertEquals(FrontendVersionInterface::FRONTEND_VERSION_DEFAULT, $frontendVersion->getVersion($frontend));
    $frontendVersion->setVersion($frontend, '1.2.3');
    $this->assertEquals('1.2.3', $frontendVersion->getVersion($frontend));
    $frontendVersion->resetVersion($frontend);
    $this->assertEquals(FrontendVersionInterface::FRONTEND_VERSION_DEFAULT, $frontendVersion->getVersion($frontend));
  }

}
