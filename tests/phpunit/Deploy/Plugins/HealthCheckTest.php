<?php

namespace Tests\Deploy\Plugins;

use Drupal\va_gov_backend\Deploy\Plugin\HealthCheck;
use Drupal\va_gov_backend\Deploy\SuccessHTTPException;
use Symfony\Component\HttpFoundation\Request;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Test for Deploy mode health check plugin.
 *
 * @group unit
 * @group all
 *
 * @covers \Drupal\va_gov_backend\Deploy\Plugin\HealthCheck
 */
class HealthCheckTest extends VaGovUnitTestBase {

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
  public function testUserAgentPrometheus() {
    $request = Request::create('/any-path');
    $request->headers->set('User-Agent', HealthCheck::VAGOV_DOWNTIME_DETECT_USER_AGENT_PROMETHEUS);

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

  /**
   * Add additional downtime user-agent.
   *
   * Two different downtime user agents are required since we want uptime graphs
   * to work correctly in both Grafana and Datadog. The addition of a Datadog
   * deploy user-agent is to quell alerts during deploys.
   * Prometheus no longer alerts.
   */
  public function testUserAgentDatadog() {
    $request = Request::create('/any-path');
    $request->headers->set('User-Agent', HealthCheck::VAGOV_DOWNTIME_DETECT_USER_AGENT_DATADOG);

    $bad_request = Request::createFromGlobals();

    $plugin = new HealthCheck();
    static::assertTrue(
      $plugin->match($request),
      'datadog user agent triggers plugin'
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
