<?php

namespace test\phpunit\Deploy\Plugins;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Deploy\Plugin\HealthCheck;
use Drupal\va_gov_backend\Deploy\SuccessHTTPException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test for Deploy mode health check plugin.
 *
 * @covers \Drupal\va_gov_backend\Deploy\Plugin\HealthCheck
 */
class HealthCheckTest extends UnitTestCase {

  /**
   * Test the Healthcheck plugin.
   *
   * @covers \Drupal\va_gov_backend\Deploy\Plugin\HealthCheck::match
   */
  public function testHealthPath() {
    $request = Request::create('/health');
    $bad_request = Request::create('/user');

    $plugin = new HealthCheck();
    static::assertTrue(
      $plugin->match($request),
      '/health path triggers plugin'
    );

    static::assertFalse(
      $plugin->match($bad_request),
      'non /user path does not trigger plugin.'
    );

    $exception = new SuccessHTTPException('Everything is awesome');
    $this->expectExceptionObject($exception);
    $plugin->run($request, 'path', 'path');
  }

  /**
   * Test the user agent.
   *
   * @covers \Drupal\va_gov_backend\Deploy\Plugin\HealthCheck::match
   */
  public function testUserAgent() {
    $request = Request::create('/any-path');
    $request->headers->set('User-Agent', HealthCheck::VAGOV_DOWNTIME_DETECT_USER_AGENT);

    $bad_request = Request::createFromGlobals();

    $plugin = new HealthCheck();
    static::assertTrue(
      $plugin->match($request),
      'prometheus user agent triggers plugin'
    );

    static::assertFalse(
      $plugin->match($bad_request),
      'default request objects should not trigger the plugin.'
    );

    $exception = new SuccessHTTPException('Everything is awesome');
    $this->expectExceptionObject($exception);
    $plugin->run($request, 'path', 'path');
  }

}
