<?php

namespace Tests\va_gov_github\unit\Api\Client;

use Drupal\va_gov_github\Api\Client\ApiClient;
use Github\Api\Repo;
use Github\Api\Repository\Actions\WorkflowRuns;
use Github\Api\Repository\Actions\Workflows;
use Github\Api\Search;
use Github\AuthMethod;
use Github\Client as RawApiClient;
use Prophecy\Argument;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\RawGitHubApiClientTrait;

/**
 * Unit test of the API Client class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_github\Api\Client\ApiClient
 */
class ApiClientTest extends VaGovUnitTestBase {

  use RawGitHubApiClientTrait;

  /**
   * Get JSON fixture from `./fixtures/<whatever>.json`.
   *
   * @param string $fixtureName
   *   The fixture name.
   *
   * @return array|string
   *   The fixture.
   */
  protected function getFixture(string $fixtureName): array|string {
    $fixturePath = __DIR__ . '/fixtures/' . $fixtureName . '.json';
    $fixture = json_decode(file_get_contents($fixturePath), TRUE);
    return $fixture;
  }

  /**
   * Test that we can construct a client.
   *
   * @covers ::__construct
   * @covers ::authenticate
   */
  public function testConstruct() {
    $login = 'fake_token';
    $password = NULL;
    $method = AuthMethod::ACCESS_TOKEN;
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $builder = $this->getHttpClientBuilder($login, $password, $method);
    $rawApiClient = $this->getRawApiClientWithBuilder($builder);
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $this->assertInstanceOf(ApiClient::class, $client);
    // Should not throw.
    $client->authenticate();
  }

  /**
   * Test the get() method.
   *
   * @param string $fixtureName
   *   The fixture name.
   * @param array|object|string|null $expected
   *   The expected result.
   *
   * @covers ::get
   * @dataProvider getDataProvider
   */
  public function testGet(string $fixtureName, array|string|null $expected) {
    $responseValue = $this->getFixture($fixtureName);
    $httpClientResponse = $this->getPsr7Response($responseValue);
    $login = 'fake_token';
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $httpClient = $this->getHttpMethodsMock(['get']);
    $httpClient
      ->expects($this->any())
      ->method('get')
      ->with('/fake_endpoint')
      ->willReturn($httpClientResponse);
    $rawApiClient = $this->getRawApiClientWithHttpClient($httpClient);
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $response = $client->get('/fake_endpoint');
    $this->assertEquals($responseValue, $response);
  }

  /**
   * Data provider for testGet().
   *
   * @return array
   *   The data.
   */
  public function getDataProvider(): array {
    return [
      ['fake_endpoint.array',
        [
          TRUE,
        ],
      ],
      ['fake_endpoint.object',
        [
          'test' => TRUE,
        ],
      ],
      ['fake_endpoint.string', 'test_string'],
    ];
  }

  /**
   * Test getWorkflowRuns().
   *
   * @covers ::getWorkflowRuns
   */
  public function testGetWorkflowRuns() {
    $login = 'fake_token';
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $actionName = 'content-build.yml';
    $parameters = [];

    $expected = $this->getFixture('workflow_runs.all');

    $workflowRunsProphecy = $this->prophesize(WorkflowRuns::class);
    $workflowRunsProphecy
      ->listRuns($owner, $repository, $actionName, $parameters)
      ->willReturn($expected)
      ->shouldBeCalled();
    $workflowRuns = $workflowRunsProphecy->reveal();

    $repoProphecy = $this->prophesize(Repo::class);
    $repoProphecy
      ->workflowRuns()
      ->willReturn($workflowRuns)
      ->shouldBeCalled();
    $repo = $repoProphecy->reveal();

    $rawApiClientProphecy = $this->prophesize(RawApiClient::class);
    $rawApiClientProphecy
      ->repositories()
      ->willReturn($repo)
      ->shouldBeCalled();

    $rawApiClient = $rawApiClientProphecy->reveal();
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $workflowRuns = $client->getWorkflowRuns($actionName, $parameters);
    $this->assertEquals($expected, $workflowRuns);
  }

  /**
   * Test searchIssues().
   *
   * @covers ::searchIssues
   */
  public function testSearchIssues() {
    $login = 'fake_token';
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $searchString = 'fake_search';
    $sortField = 'fake_sort_field';
    $order = 'fake_order';

    $expected = $this->getFixture('search_issues.all');

    $searchProphecy = $this->prophesize(Search::class);
    $searchProphecy
      ->issues(Argument::type('string'), $sortField, $order)
      ->willReturn($expected)
      ->shouldBeCalled();
    $search = $searchProphecy->reveal();

    $rawApiClientProphecy = $this->prophesize(RawApiClient::class);
    $rawApiClientProphecy
      ->search()
      ->willReturn($search)
      ->shouldBeCalled();

    $rawApiClient = $rawApiClientProphecy->reveal();
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $issues = $client->searchIssues($searchString, $sortField, $order);
    $this->assertEquals($expected, $issues);
  }

  /**
   * Test searchPullRequests().
   *
   * @covers ::searchPullRequests
   */
  public function testSearchPullRequests() {
    $login = 'fake_token';
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $searchString = 'fake_search';
    $sortField = 'fake_sort_field';
    $order = 'fake_order';
    $parameters = [];

    $expected = $this->getFixture('search_prs.all');

    $searchProphecy = $this->prophesize(Search::class);
    $searchProphecy
      ->issues(Argument::type('string'), $sortField, $order)
      ->willReturn($expected)
      ->shouldBeCalled();
    $search = $searchProphecy->reveal();

    $rawApiClientProphecy = $this->prophesize(RawApiClient::class);
    $rawApiClientProphecy
      ->search()
      ->willReturn($search)
      ->shouldBeCalled();

    $rawApiClient = $rawApiClientProphecy->reveal();
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $pullRequests = $client->searchPullRequests($searchString, $sortField, $order, $parameters);
    $this->assertEquals($expected, $pullRequests);
  }

  /**
   * Test triggerRepositoryDispatchEvent().
   *
   * @covers ::triggerRepositoryDispatchEvent
   */
  public function testTriggerRepositoryDispatchEvent() {
    $login = 'fake_token';
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $eventType = 'fake_event_type';
    $parameters = [];

    $repoProphecy = $this->prophesize(Repo::class);
    $repoProphecy
      ->dispatch($owner, $repository, $eventType, $parameters)
      ->willReturn('')
      ->shouldBeCalled();
    $repo = $repoProphecy->reveal();

    $rawApiClientProphecy = $this->prophesize(RawApiClient::class);
    $rawApiClientProphecy
      ->repositories()
      ->willReturn($repo)
      ->shouldBeCalled();

    $rawApiClient = $rawApiClientProphecy->reveal();
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $client->triggerRepositoryDispatchEvent($eventType, $parameters);
  }

  /**
   * Test triggerWorkflowDispatchEvent().
   *
   * @covers ::triggerWorkflowDispatchEvent
   */
  public function testTriggerWorkflowDispatchEvent() {
    $login = 'fake_token';
    $owner = 'fake_owner';
    $repository = 'fake_repository';
    $workflowId = 'fake_workflow_id';
    $reference = 'fake_reference';
    $parameters = [];

    $workflowsProphecy = $this->prophesize(Workflows::class);
    $workflowsProphecy
      ->dispatches($owner, $repository, $workflowId, $reference, $parameters)
      ->willReturn('')
      ->shouldBeCalled();
    $workflows = $workflowsProphecy->reveal();

    $repoProphecy = $this->prophesize(Repo::class);
    $repoProphecy
      ->workflows()
      ->willReturn($workflows)
      ->shouldBeCalled();
    $repo = $repoProphecy->reveal();

    $rawApiClientProphecy = $this->prophesize(RawApiClient::class);
    $rawApiClientProphecy
      ->repositories()
      ->willReturn($repo)
      ->shouldBeCalled();

    $rawApiClient = $rawApiClientProphecy->reveal();
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $client->triggerWorkflowDispatchEvent($workflowId, $reference, $parameters);
  }

}
