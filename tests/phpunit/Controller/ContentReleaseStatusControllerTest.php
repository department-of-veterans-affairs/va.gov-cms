<?php

namespace tests\phpunit\Controller;

use Drupal\va_gov_backend\Controller\ContentReleaseStatusController;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Provides automated tests for the va_gov_backend module.
 */
class ContentReleaseStatusControllerTest extends ExistingSiteBase {

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
  public static function getInfo() {
    return [
      'name' => "va_gov_backend ContentReleaseStatusController's controller functionality",
      'description' => 'Test Unit for module va_gov_backend and controller ContentReleaseStatusController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->newContainer = new ContainerBuilder();
    $this->newContainer->set('date.formatter', \Drupal::service('date.formatter'));
    $this->newContainer->set('renderer', \Drupal::service('renderer'));
  }

  /**
   * Tests content release status controller.
   */
  public function testContentReleaseStatusController() {
    $today = strtotime('today 8am');
    $yesterday = strtotime('yesterday 1405');
    $four_days_ago = strtotime('-4 day 1038');
    $this->mockClient(
      new Response(200, [], $this->generateBodyForTime($today)),
      new Response(200, [], $this->generateBodyForTime($yesterday)),
      new Response(200, [], $this->generateBodyForTime($four_days_ago))
    );

    $contentReleaseStatusController = ContentReleaseStatusController::create($this->newContainer);

    $response = $contentReleaseStatusController->getLastReleaseStatus();

    $this->assertStringContainsString('VA.gov last updated<br />today at 08:00 am', $response->getContent());

    $response = $contentReleaseStatusController->getLastReleaseStatus();
    $this->assertStringContainsString('VA.gov last updated<br />yesterday at 02:05 pm', $response->getContent());

    $response = $contentReleaseStatusController->getLastReleaseStatus();
    $this->assertStringContainsString('VA.gov last updated<br />4 days ago at 10:38 am', $response->getContent());
  }

  /**
   * Generate a response body matching va.gov/BUILD.txt.
   *
   * @param int $timestamp
   *   A Unix timestamp.
   *
   * @return string
   *   Response body text.
   */
  protected function generateBodyForTime(int $timestamp) {
    return "BUILDTYPE=vagovprod\nNODE_ENV=production\nBRANCH_NAME=null\nCHANGE_TARGET=null\nBUILD_ID=799\nBUILD_NUMBER=799\nREF=80ff0a2438f1a0d038ab8637f553a83792596bd4\nBUILDTIME={$timestamp}";
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

    $this->newContainer->set('http_client', $this->mockClient);
  }

}
