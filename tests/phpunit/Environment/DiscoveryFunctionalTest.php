<?php

namespace tests\phpunit\Environment;

use Tests\Support\Classes\VaGovExistingSiteBase;
use Drupal\va_gov_environment\Service\DiscoveryInterface;

/**
 * Functional test of the Environment Discovery service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_environment\Service\Discovery
 */
class DiscoveryFunctionalTest extends VaGovExistingSiteBase {

  /**
   * Test that the raw environment matches one of our expected values.
   *
   * DDEV uses the string "local" rather than "ddev". This will change in the
   * processed environment.
   *
   * @covers ::getRawEnvironment
   */
  public function testGetRawEnvironment() {
    $rawEnvironment = $this->container->get('va_gov_environment.discovery')->getRawEnvironment();
    $rawEnvironments = [
      'local',
      'tugboat',
      'staging',
      'prod',
    ];
    $this->assertContains($rawEnvironment, $rawEnvironments);
  }

  /**
   * Test that the processed environment matches one of our expected values.
   *
   * @covers ::getEnvironment
   */
  public function testGetEnvironment() {
    $environment = $this->container->get('va_gov_environment.discovery')->getEnvironment();
    $this->assertContains($environment, DiscoveryInterface::ENVIRONMENTS);
  }

  /**
   * Test that CMS-TEST is detected correctly.
   *
   * @covers ::isCmsTest
   */
  public function testIsCmsTest() {
    $isCmsTest = $this->container->get('va_gov_environment.discovery')->isCmsTest();
    $expected = getenv('CMS_APP_NAME') ?? '' === 'cms-test';
    $this->assertEquals($expected, $isCmsTest);
  }

}
