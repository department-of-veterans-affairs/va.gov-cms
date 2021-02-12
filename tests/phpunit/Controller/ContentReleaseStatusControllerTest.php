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
   * The container object.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $newContainer;

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
  public function setUp() : void {
    parent::setUp();

    $this->newContainer = new ContainerBuilder();
    $this->newContainer->set('date.formatter', \Drupal::service('date.formatter'));
    $this->newContainer->set('renderer', \Drupal::service('renderer'));
  }

  /**
   * Tests content release status controller.
   *
   * @dataProvider provideContentReleaseData
   */
  public function testContentReleaseStatusController(
    string $responseBody,
    string $expectedResult
  ) : void {
    $this->mockClient(new Response(200, [], $responseBody));
    $this->assertStringContainsString(
      $expectedResult,
      ContentReleaseStatusController::create($this->newContainer)->getLastReleaseStatus()->getContent()
    );
  }

  /**
   * Data provider for testContentReleaseStatusController.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function provideContentReleaseData() : \Generator {
    date_default_timezone_set('America/New_York');

    $today = strtotime('today 8am');
    yield 'Last content release happened today' => [
      $this->generateBodyForTime($today),
      'VA.gov last updated<br />today at 08:00 am',
    ];

    $yesterday = strtotime('yesterday 1405');
    yield 'Last content release happened yesterday' => [
      $this->generateBodyForTime($yesterday),
      'VA.gov last updated<br />yesterday at 02:05 pm',
    ];

    $four_days_ago = strtotime('-4 day 1038');
    yield 'Last content release happened four days ago' => [
      $this->generateBodyForTime($four_days_ago),
      'VA.gov last updated<br />4 days ago at 10:38 am',
    ];
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
  protected function generateBodyForTime(int $timestamp) : string {
    return "BUILDTYPE=vagovprod\nNODE_ENV=production\nBRANCH_NAME=null\nCHANGE_TARGET=null\nBUILD_ID=799\nBUILD_NUMBER=799\nREF=80ff0a2438f1a0d038ab8637f553a83792596bd4\nBUILDTIME={$timestamp}";
  }

  /**
   * Mock the http client.
   *
   * @param \GuzzleHttp\Psr7\Response $responses
   *   Guzzle responses.
   */
  protected function mockClient(Response ...$responses) : void {
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
