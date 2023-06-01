<?php

namespace tests\phpunit\Service;

use Tests\Support\Classes\VaGovUnitTestBase;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_consumers\GitHub\GitHubClientFactory;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

/**
 * Test the GitHubClient service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_consumers\GitHub\GitHubClient
 */
class GitHubClientServiceTest extends VaGovUnitTestBase {

  /**
   * Tests the searchPullRequests() method.
   *
   * @covers ::searchPullRequestsRaw
   * @covers ::searchPullRequests
   */
  public function testSearchPullRequests() {
    $settings = new Settings([
      'key' => 'test_token',
    ]);
    $httpClientProphecy = $this->prophesize(HttpClient::class);
    $httpClientProphecy->request('GET', Argument::type('string'), Argument::type('array'))->will(function ($args) {
      $statusCode = 200;
      $headers = ['X-Foo' => 'Bar'];
      $body = '{"total_count": 1, "items": [{"number": 1, "title": "Test PR"}]}';
      $response = new Response($statusCode, $headers, $body);
      return $response;
    });
    $httpClient = $httpClientProphecy->reveal();
    $tokenSettingsName = 'key';
    $repositoryPath = 'department-of-veterans-affairs/va.gov-cms-test';
    $factory = new GitHubClientFactory($settings, $httpClient);
    $client = $factory->getClient($repositoryPath, $tokenSettingsName);
    $searchTerm = 'dependabot';
    $response = $client->searchPullRequestsRaw($searchTerm);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Bar', $response->getHeaderLine('X-Foo'));
    $data = $client->searchPullRequests($searchTerm);
    $this->assertEquals([["number" => 1, "title" => "Test PR"]], $data['items']);
  }

  /**
   * Tests the triggerWorkflow() method.
   *
   * @covers ::triggerWorkflowRaw
   * @covers ::triggerWorkflow
   */
  public function testTriggerWorkflow() {
    $settings = new Settings([
      'key' => 'test_token',
    ]);
    $httpClientProphecy = $this->prophesize(HttpClient::class);
    $httpClientProphecy->request('POST', Argument::type('string'), Argument::type('array'))->will(function ($args) {
      $statusCode = 204;
      $headers = ['X-Foo' => 'Bar'];
      $response = new Response($statusCode, $headers);
      return $response;
    });
    $httpClient = $httpClientProphecy->reveal();
    $tokenSettingsName = 'key';
    $repositoryPath = 'department-of-veterans-affairs/va.gov-cms-test';
    $factory = new GitHubClientFactory($settings, $httpClient);
    $client = $factory->getClient($repositoryPath, $tokenSettingsName);
    $workflowName = 's3-backup-retention.yml';
    $ref = 'main';
    $params = [];
    $response = $client->triggerWorkflowRaw($workflowName, $ref, $params);
    $this->assertEquals(204, $response->getStatusCode());
    $this->assertEquals('Bar', $response->getHeaderLine('X-Foo'));
  }

  /**
   * Tests the listWorkflowRuns() method.
   *
   * @covers ::listWorkflowRunsRaw
   * @covers ::listWorkflowRuns
   */
  public function testListWorkflowRuns() {
    $settings = new Settings([
      'key' => 'test_tokens',
    ]);
    $httpClientProphecy = $this->prophesize(HttpClient::class);
    $httpClientProphecy->request('GET', Argument::type('string'), Argument::type('array'))->will(function ($args) {
      $statusCode = 200;
      $headers = ['X-Foo' => 'Bar'];
      $body = json_encode([
        "total_count" => 1,
        "workflow_runs" => [
          [
            "id" => 4848584790,
            "name" => "VAgov CMS S3 Backup Manager",
            "node_id" => "WFR_kwLOD13zBc8AAAABIP-IVg",
            "head_branch" => "main",
            "head_sha" => "02724d5127ff03e089472224b074647d787ed385",
            "path" => ".github\/workflows\/s3-backup-retention.yml",
            "display_title" => "VAgov CMS S3 Backup Manager",
            "run_number" => 443,
          ],
        ],
      ]);
      $response = new Response($statusCode, $headers, $body);
      return $response;
    });
    $httpClient = $httpClientProphecy->reveal();
    $tokenSettingsName = 'key';
    $repositoryPath = 'department-of-veterans-affairs/va.gov-cms-test';
    $factory = new GitHubClientFactory($settings, $httpClient);
    $client = $factory->getClient($repositoryPath, $tokenSettingsName);
    $workflowName = 's3-backup-retention.yml';
    $params = [];
    $response = $client->listWorkflowRunsRaw($workflowName, $params);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Bar', $response->getHeaderLine('X-Foo'));
    $runs = $client->listWorkflowRuns($workflowName, $params);
    $this->assertEquals(4848584790, $runs['workflow_runs'][0]['id']);
  }

  /**
   * Tests the repositoryDispatchWorkflow() method.
   *
   * @covers ::repositoryDispatchWorkflowRaw
   * @covers ::repositoryDispatchWorkflow
   */
  public function testRepositoryDispatchWorkflow() {
    $settings = new Settings([
      'key' => 'test_token',
    ]);
    $httpClientProphecy = $this->prophesize(HttpClient::class);
    $httpClientProphecy->request('POST', Argument::type('string'), Argument::type('array'))->will(function ($args) {
      $statusCode = 204;
      $headers = ['X-Foo' => 'Bar'];
      $response = new Response($statusCode, $headers);
      return $response;
    });
    $httpClient = $httpClientProphecy->reveal();
    $tokenSettingsName = 'key';
    $repositoryPath = 'department-of-veterans-affairs/va.gov-cms-test';
    $factory = new GitHubClientFactory($settings, $httpClient);
    $client = $factory->getClient($repositoryPath, $tokenSettingsName);
    $eventType = 's3-backup-retention';
    $params = [];
    $response = $client->repositoryDispatchWorkflowRaw($eventType, (object) $params);
    $this->assertEquals(204, $response->getStatusCode());
    $this->assertEquals('Bar', $response->getHeaderLine('X-Foo'));
  }

}
