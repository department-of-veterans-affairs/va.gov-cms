<?php

namespace tests\phpunit\FrontendBuild\Brd;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\va_gov_build_trigger\Service\SystemsManagerClientInterface;
use GuzzleHttp\ClientInterface as GuzzleHttpClientInterface;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

/**
 * Tests of the Jenkins client.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Service\JenkinsClient
 */
class JenkinsClientTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'va_gov_build_trigger',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $settings['jenkins_build_env'] = 'TEST';
    $settings['jenkins_build_job_host'] = 'http://jenkins.vfs.va.gov';
    $settings['va_cms_bot_github_username'] = 'va-cms-bot';
    $settings['va_gov_frontend_url'] = 'https://staging.va.gov';
    $settings['va_gov_frontend_build_type'] = 'brd';
    $settings['jenkins_build_job_path'] = '/job/builds/job/vets-website-content-vagov' . $settings['jenkins_build_env'];
    $settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
    $settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];
    $this->container->set('settings', new Settings($settings));
    $systemsManagerClientProphecy = $this->prophesize(SystemsManagerClientInterface::class);
    $systemsManagerClientProphecy
      ->getJenkinsApiToken()
      ->willReturn('MY_PRETEND_JENKINS_API_TOKEN');
    $this->container->set('va_gov_build_trigger.systems_manager_client', $systemsManagerClientProphecy->reveal());

  }

  /**
   * Test the client's request dispatch.
   *
   * @covers ::requestFrontendBuild
   */
  public function testRequestFrontendBuild() {
    $expectedUrl = 'http://jenkins.vfs.va.gov';
    $expectedUsername = 'va-cms-bot';
    $expectedPassword = 'MY_PRETEND_JENKINS_API_TOKEN';
    $httpClientProphecy = $this->prophesize(GuzzleHttpClientInterface::class);
    $httpClientProphecy
      ->request(Argument::exact('POST'), Argument::exact($expectedUrl), Argument::type('array'))
      ->willReturn(new Response(201))
      ->shouldBeCalled();
    $httpClient = $httpClientProphecy->reveal();
    $this->container->set('va_gov_build_trigger.jenkins_http_client', $httpClient);
    $jenkinsClient = \Drupal::service('va_gov_build_trigger.jenkins_client');
    $jenkinsClient->requestFrontendBuild(NULL, FALSE, $httpClient);
  }

}
