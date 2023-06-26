<?php

namespace tests\phpunit\va_gov_content_release;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Strategy Plugin Manager service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManager
 */
class StrategyPluginManagerTest extends VaGovExistingSiteBase {

  /**
   * Test that the Strategy Plugin Manager service is available.
   *
   * @covers ::getDefinitions
   */
  public function testGetDefinitions() {
    $definitions = $this->container->get('plugin.manager.va_gov_content_release.strategy')->getDefinitions();
    $this->assertNotEmpty($definitions);
  }

}
