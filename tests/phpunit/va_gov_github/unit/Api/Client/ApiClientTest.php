<?php

namespace Tests\va_gov_github\unit\Api\Client;

use Drupal\va_gov_github\Api\Client\ApiClient;
use Tests\Support\Traits\RawGitHubApiClientTrait;
use Tests\Support\Classes\VaGovUnitTestBase;
use Github\AuthMethod;
use Github\Api\Repo;
use Github\Api\Repository\Actions\WorkflowRuns;
use Github\Client as RawApiClient;

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
      ->willReturn($expected);
    $workflowRuns = $workflowRunsProphecy->reveal();

    $repoProphecy = $this->prophesize(Repo::class);
    $repoProphecy
      ->workflowRuns()
      ->willReturn($workflowRuns);
    $repo = $repoProphecy->reveal();

    $rawApiClientProphecy = $this->prophesize(RawApiClient::class);
    $rawApiClientProphecy
      ->repositories()
      ->willReturn($repo);

    $rawApiClient = $rawApiClientProphecy->reveal();
    $client = ApiClient::createWithRawApiClient($rawApiClient, $owner, $repository, $login);
    $workflowRuns = $client->getWorkflowRuns($actionName, $parameters);
    $this->assertEquals($expected, $workflowRuns);
  }

}
