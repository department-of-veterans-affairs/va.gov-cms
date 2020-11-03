<?php

namespace tests\phpunit;

use Drupal\Core\Site\Settings;
use Drupal\va_gov_backend\Service\VaGovUrl;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test the VaGovUrl Service.
 */
class VaGovUrlServiceTest extends ExistingSiteBase {

  /**
   * History of requests/responses.
   *
   * @var array
   */
  protected $history = [];

  /**
   * Mock client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $mockClient;

  /**
   * Settings array.
   *
   * @var array
   */
  protected $config = array();

  /**
   * Settings Service
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->container = new ContainerBuilder();
    $this->config = ['hash_salt' => 'SCVSPZNSKKK5XCRJ1WLE'];
    $this->settings = new Settings($this->config);
  }

  /**
   * Verify getVaGovFrontEndUrl method.
   *
   * @group functional
   * @group all
   */
  public function testGetVaGovFrontEndUrl() {
    $this->mockClient();
    $vaGovUrl = new VaGovUrl($this->mockClient, $this->settings);
    $this->assertEquals('https://www.va.gov', $vaGovUrl->getVaGovFrontEndUrl());
  }

  /**
   * Verify that the prod URL may be over-ridden with settings.
   *
   * @group functional
   * @group all
   */
  public function testOverrideVaGovFrontEndUrl() {
    $this->mockClient();
    $this->config = ['va_gov_frontend_url' => 'https://other.va.gov'];
    $this->settings = new Settings($this->config);
    $vaGovUrl = new VaGovUrl($this->mockClient, $this->settings);
    $this->assertNotEquals('https://www.va.gov', $vaGovUrl->getVaGovFrontEndUrl());
    $this->assertEquals('https://other.va.gov', $vaGovUrl->getVaGovFrontEndUrl());
  }

  /**
   * Verify getVaGovFrontEndUrlForEntity method.
   *
   * @group functional
   * @group all
   */
  public function testGetVaGovFrontEndUrlForEntity() {
    $this->mockClient();
    $vaGovUrl = new VaGovUrl($this->mockClient, $this->settings);

    $author = $this->createUser();
    $system_node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $system_node->setPublished()->save();
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $system_node->id());
    $this->assertEquals('/va-test-health-care', $url_alias);

    $this->assertEquals('https://www.va.gov/va-test-health-care', $vaGovUrl->getVaGovFrontEndUrlForEntity($system_node));
  }

  /**
   * Verify getVaGovFrontEndUrlForEntityIsLive method.
   *
   * @group functional
   * @group all
   */
  public function testVaGovFrontEndUrlForEntityIsLive() {
    $this->mockClient(new Response('200'), new Response('404'));
    $vaGovUrl = new VaGovUrl($this->mockClient, $this->settings);

    $author = $this->createUser();
    $system_node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $system_node->setPublished()->save();

    $this->assertTrue($vaGovUrl->vaGovFrontEndUrlForEntityIsLive($system_node));
    $this->assertFalse($vaGovUrl->vaGovFrontEndUrlForEntityIsLive($system_node));
  }

  /**
   * Mock the http client.
   */
  protected function mockClient(Response ...$responses) {
    if (!isset($this->mockClient)) {
      // Create a mock and queue responses.
      $mock = new MockHandler($responses);

      $handler_stack = HandlerStack::create($mock);
      $history = Middleware::history($this->history);
      $handler_stack->push($history);
      $this->mockClient = new Client(['handler' => $handler_stack]);
    }

    $this->container->set('http_client', $this->mockClient);
  }

}
