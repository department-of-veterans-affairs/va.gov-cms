<?php

namespace tests\phpunit\FrontendBuild\Brd;

use Aws\MockHandler;
use Aws\Result;
use Aws\Ssm\SsmClient;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_build_trigger\Service\BuildTimeRecorderInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests of the BRD environment plugin.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Plugin\VAGov\Environment\BRD
 */
class BrdEnvironmentTest extends ExistingSiteBase {

  /**
   * Stash variable to store the original service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $originalSettings;

  /**
   * Stash variable to store the original service.
   *
   * @var \Aws\Ssm\SsmClient
   */
  protected $originalSsmClient;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Mock settings.
    $settings['jenkins_build_env'] = 'TEST';
    $settings['jenkins_build_job_host'] = 'http://jenkins.vfs.va.gov';
    $settings['va_cms_bot_github_username'] = 'va-cms-bot';
    $settings['va_gov_frontend_url'] = 'https://staging.va.gov';
    $settings['va_gov_frontend_build_type'] = 'brd';
    $settings['jenkins_build_job_path'] = '/job/builds/job/vets-website-content-vagov' . $settings['jenkins_build_env'];
    $settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
    $settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];

    $this->originalSettings = $this->container->get('settings');
    $this->container->set('settings', new Settings($settings));

    // Mock the AWS SSM interface.
    $mock = new MockHandler();
    $mock->append(new Result([
      'Parameter' => [
        'ARN' => '<string>',
        'DataType' => '<string>',
        'LastModifiedDate' => 'some date',
        'Name' => '<string>',
        'Selector' => '<string>',
        'SourceResult' => '<string>',
        'Type' => 'String|StringList|SecureString',
        'Value' => 'THIS IS MY IMAGINARY JENKINS API TOKEN',
        'Version' => 123,
      ],
    ]));
    $ssmClient = new SsmClient([
      'version' => 'latest',
      'region' => 'us-gov-west-1',
      'handler' => $mock,
      'credentials' => [
        'key'    => 'FAKE AWS ACCESS KEY',
        'secret' => 'FAKE AWS SECRET KEY',
      ],
    ]);
    $this->originalSsmClient = $this->container->get('va_gov_build_trigger.aws_ssm_client');
    $this->container->set('va_gov_build_trigger.aws_ssm_client', $ssmClient);
    $this->originalJenkinsHttpClient = $this->container->get('va_gov_build_trigger.jenkins_http_client');
    $this->originalBuildTimeRecorder = $this->container->get('va_gov_build_trigger.build_time_recorder');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->container->set('settings', $this->originalSettings);
    $this->container->set('va_gov_build_trigger.aws_ssm_client', $this->originalSsmClient);
    $this->container->set('va_gov_build_trigger.jenkins_http_client', $this->originalJenkinsHttpClient);
    $this->container->set('va_gov_build_trigger.build_time_recorder', $this->originalBuildTimeRecorder);
    parent::tearDown();
  }

  /**
   * Test the client's request dispatch.
   *
   * @covers ::triggerFrontendBuild
   */
  public function testTriggerFrontendBuild() {
    $httpClientProphecy = $this->prophesize(ClientInterface::class);
    $httpClientProphecy
      ->request(Argument::exact('POST'), Argument::type('string'), Argument::type('array'))
      ->willReturn(new Response(201))
      ->shouldBeCalled();
    $httpClient = $httpClientProphecy->reveal();
    $this->container->set('va_gov_build_trigger.jenkins_http_client', $httpClient);

    $buildTimeRecorderProphecy = $this->prophesize(BuildTimeRecorderInterface::class);
    $buildTimeRecorderProphecy
      ->recordBuildTime()
      ->shouldBeCalled();
    $this->container->set('va_gov_build_trigger.build_time_recorder', $buildTimeRecorderProphecy->reveal());
    $plugin = $this->container->get('plugin.manager.va_gov.environment')->createInstance('brd');
    $plugin->triggerFrontendBuild();
  }

}
