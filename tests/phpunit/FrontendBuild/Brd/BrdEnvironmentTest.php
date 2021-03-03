<?php

namespace tests\phpunit\FrontendBuild\Brd;

use Aws\MockHandler;
use Aws\Result;
use Aws\Ssm\SsmClient;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

/**
 * Tests of the BRD environment plugin.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Plugin\VAGov\Environment\BRD
 */
class BrdEnvironmentTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'advancedqueue',
    'datetime',
    'field',
    'node',
    'system',
    'user',
    'va_gov_build_trigger',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Nodes for testing last-built date update.
    static::installEntitySchema('node');
    \Drupal::moduleHandler()
      ->loadInclude('node', 'install');
    NodeType::create([
      'type' => 'build_time_recorder_test_node',
      'label' => 'Build Time Recorder Test Node',
    ])
      ->save();
    FieldStorageConfig::create([
      'field_name' => 'field_page_last_built',
      'entity_type' => 'node',
      'type' => 'datetime',
      'settings' => [
        'datetime_type' => 'datetime',
      ],
      'cardinality' => 1,
    ])
      ->save();
    FieldConfig::create([
      'field_name' => 'field_page_last_built',
      'entity_type' => 'node',
      'bundle' => 'build_time_recorder_test_node',
      'label' => 'Page Last Built',
    ])
      ->save();
    static::createUser([], [
      'access content',
      'administer site configuration',
    ]);
    $this->node1 = Node::create([
      'type' => 'build_time_recorder_test_node',
      'title' => '1',
      'field_page_last_built' => 1,
    ])
      ->save();

    // Mock settings.
    $settings['jenkins_build_env'] = 'TEST';
    $settings['jenkins_build_job_host'] = 'http://jenkins.vfs.va.gov';
    $settings['va_cms_bot_github_username'] = 'va-cms-bot';
    $settings['va_gov_frontend_url'] = 'https://staging.va.gov';
    $settings['va_gov_frontend_build_type'] = 'brd';
    $settings['jenkins_build_job_path'] = '/job/builds/job/vets-website-content-vagov' . $settings['jenkins_build_env'];
    $settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
    $settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];
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
    $this->container->set('va_gov_build_trigger.aws_ssm_client', $ssmClient);

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

    $plugin = $this->container->get('plugin.manager.va_gov.environment')->createInstance('brd');
    $plugin->triggerFrontendBuild();

    // Test that the last-built time of a node has been updated correctly.
    $node1 = Node::load(1);
    $this->assertNotEquals($node1->get('field_page_last_built')->getValue()[0]['value'], '1970-01-01T00:00:01');
  }

}
