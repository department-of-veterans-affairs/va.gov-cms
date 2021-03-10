<?php

namespace tests\phpunit\BuildTrigger\FrontendBuild;

use Drupal\KernelTests\KernelTestBase;

/**
 * Kernel tests for the web build status service.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\FrontendBuild\Status
 */
class StatusTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'va_gov_build_trigger',
  ];

  /**
   * Test that the service really does what it claims to do.
   *
   * @covers ::getStatus
   * @covers ::setStatus
   */
  public function testWebBuildStatus() {
    $service = $this->container->get('va_gov.build_trigger.web_build_status');
    $this->assertFalse($service->getWebBuildStatus());
    $service->setWebBuildStatus(TRUE);
    $this->assertTrue($service->getWebBuildStatus());
    $service->setWebBuildStatus(FALSE);
    $this->assertFalse($service->getWebBuildStatus());
  }

}
