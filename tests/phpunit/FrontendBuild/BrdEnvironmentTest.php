<?php

namespace tests\phpunit\FrontendBuild;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests of the BRD environment plugin.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Plugin\VAGov\Environment\BRD
 */
class BrdEnvironmentTest extends ExistingSiteBase {

  /**
   * @covers ::getJenkinsApiToken
   */
  public function testGetJenkinsApiToken() {
    $plugin = \Drupal::service('plugin.manager.va_gov.environment')->createInstance('brd');
    $token = $plugin->getJenkinsApiToken();
  }

}
