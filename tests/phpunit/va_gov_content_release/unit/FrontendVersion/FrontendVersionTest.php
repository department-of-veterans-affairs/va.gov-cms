<?php

namespace tests\phpunit\va_gov_environment\unit\FrontendVersion;

use Drupal\Core\State\State;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
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
   * @covers ::get
   * @covers ::set
   * @covers ::reset
   */
  public function testGetSetReset() : void {
    $state = new State(new KeyValueMemoryFactory());
    $frontendVersion = new FrontendVersion($state);
    $this->assertEquals(FrontendVersionInterface::FRONTEND_VERSION_DEFAULT, $frontendVersion->get());
    $frontendVersion->set('1.2.3');
    $this->assertEquals('1.2.3', $frontendVersion->get());
    $frontendVersion->reset();
    $this->assertEquals(FrontendVersionInterface::FRONTEND_VERSION_DEFAULT, $frontendVersion->get());
  }

}
