<?php

namespace tests\phpunit;

use Drupal\va_gov_backend\Service\VaGovUrl;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm ability to create media.
 */
class VaGovUrlTest extends ExistingSiteBase {

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->container = new ContainerBuilder();
  }

  /**
   * Verify getVaGovUrlForEnvironment method.
   *
   * @group functional
   * @group all
   */
  public function testGetVaGovUrlForEnvironment() {
    $this->mockClient();
    $vaGovUrl = new VaGovUrl($this->mockClient);
    $this->assertEquals('https://www.va.gov', $vaGovUrl->getVaGovUrlForEnvironment('prod'));
  }

  /**
   * Verify getVaGovUrlForEntity method.
   *
   * @group functional
   * @group all
   */
  public function testGetVaGovUrlForEntity() {
    $this->mockClient();
    $vaGovUrl = new VaGovUrl($this->mockClient);

    $author = $this->createUser();
    $system_node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $system_node->setPublished()->save();
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $system_node->id());
    $this->assertEquals('/va-test-health-care', $url_alias);

    $this->assertEquals('https://www.va.gov/va-test-health-care', $vaGovUrl->getVaGovUrlForEntity($system_node));
    $this->assertEquals('https://staging.va.gov/va-test-health-care', $vaGovUrl->getVaGovUrlForEntity($system_node, 'staging'));
  }

  /**
   * Verify getVaGovUrlStatusForEntity method.
   *
   * @group functional
   * @group all
   */
  public function testGetVaGovUrlStatusForEntity() {
    $this->mockClient(new Response('200'), new Response('404'));
    $vaGovUrl = new VaGovUrl($this->mockClient);

    $author = $this->createUser();
    $system_node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $system_node->setPublished()->save();

    $this->assertEquals(200, $vaGovUrl->getVaGovUrlStatusForEntity($system_node));
    $this->assertEquals(404, $vaGovUrl->getVaGovUrlStatusForEntity($system_node));
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
