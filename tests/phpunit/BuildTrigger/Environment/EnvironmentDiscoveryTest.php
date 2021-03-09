<?php

namespace tests\phpunit\BuildTrigger\Environment;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the Environment Discovery service.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
 */
class EnvironmentDiscoveryTest extends ExistingSiteBase {

  /**
   * Confirm that, regardless of value, no API calls result in errors.
   *
   * @doesNotPerformAssertions
   */
  public function testInterface() {
    $environmentDiscovery = $this->container->get('va_gov_build_trigger.environment_discovery');
    $environmentDiscovery->isBrd();
    $environmentDiscovery->isDevShop();
    $environmentDiscovery->isTugboat();
    $environmentDiscovery->isLocal();
    $environmentDiscovery->getEnvironmentId();
    $environmentDiscovery->getBuildTypeKey();
    $environmentDiscovery->getEnvironment();
    $environmentDiscovery->getWebUrl();
    $environmentDiscovery->shouldTriggerFrontendBuild();
    $environmentDiscovery->getBuildTriggerFormClass();
  }

}
