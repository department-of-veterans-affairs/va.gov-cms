<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\va_gov_build_trigger\WebBuildStatus;

/**
 * Kernel tests for the web build status service.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\WebBuildStatus
 */
class WebBuildStatusTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'va_gov_build_trigger',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $settings = [];
    $settings[WebBuildStatus::USE_CMS_EXPORT_SETTING] = 3;
    $this->container->set('settings', new Settings($settings));
  }

  /**
   * Test that the service really does what it claims to do.
   *
   * @covers ::getWebBuildStatus
   * @covers ::setWebBuildStatus
   * @covers ::enableWebBuildStatus
   * @covers ::disableWebBuildStatus
   */
  public function testWebBuildStatus() {
    $service = $this->container->get('va_gov.build_trigger.web_build_status');
    $this->assertFalse($service->getWebBuildStatus());
    $service->setWebBuildStatus(TRUE);
    $this->assertTrue($service->getWebBuildStatus());
    $service->setWebBuildStatus(FALSE);
    $this->assertFalse($service->getWebBuildStatus());
    $service->enableWebBuildStatus();
    $this->assertTrue($service->getWebBuildStatus());
    $service->disableWebBuildStatus();
    $this->assertFalse($service->getWebBuildStatus());
  }

}
