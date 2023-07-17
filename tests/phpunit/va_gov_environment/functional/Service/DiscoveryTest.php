<?php

namespace tests\phpunit\va_gov_environment\functional\Service;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Environment Discovery service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_environment\Discovery\Discovery
 */
class DiscoveryTest extends VaGovExistingSiteBase {

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
    $this->assertNotNull($environment);
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
