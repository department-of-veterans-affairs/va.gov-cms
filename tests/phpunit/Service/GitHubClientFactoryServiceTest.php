<?php

namespace tests\phpunit\Service;

use Drupal\va_gov_consumers\GitHub\GitHubClientFactory;
use Drupal\va_gov_consumers\GitHub\GitHubClientInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Client as HttpClient;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Test the GitHubClientFactory service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_consumers\GitHub\GitHubClientFactory
 */
class GitHubClientFactoryServiceTest extends VaGovUnitTestBase {

  /**
   * When the token is not set, the factory should throw an exception.
   *
   * The token is set in BRD and Tugboat, but not in local environments.
   *
   * @param string $repositoryPath
   *   The path to the repo, e.g. 'department-of-veterans-affairs/va.gov-cms'.
   * @param string $tokenSettingsName
   *   The name of the settings variable that stores the GitHub token.
   * @param string $expectedMessage
   *   The expected exception message.
   *
   * @covers ::getClient
   * @dataProvider dataProviderGetClientExceptionMessages
   */
  public function testGetClientExceptionMessages(string $repositoryPath, string $tokenSettingsName, string $expectedMessage) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($expectedMessage);
    $this->expectExceptionCode(0);
    $settings = new Settings([]);
    $httpClientProphecy = $this->prophesize(HttpClient::class);
    $httpClient = $httpClientProphecy->reveal();
    $factory = new GitHubClientFactory($settings, $httpClient);
    $factory->getClient($repositoryPath, $tokenSettingsName);
  }

  /**
   * Data provider for testGetClientExceptionMessages().
   */
  public function dataProviderGetClientExceptionMessages() {
    return [
      'empty repository name' => [
        'repositoryPath' => '',
        'tokenSettingsName' => 'va_gov_consumers.github_token',
        'expectedMessage' => 'Invalid GitHub Repository Path',
      ],
      'empty token' => [
        'repositoryPath' => 'department-of-veterans-affairs/va.gov-cms',
        'tokenSettingsName' => 'va_gov_consumers.github_token',
        'expectedMessage' => 'Invalid GitHub Token',
      ],
      'empty token setting name' => [
        'repositoryPath' => 'department-of-veterans-affairs/va.gov-cms',
        'tokenSettingsName' => '',
        'expectedMessage' => 'Invalid GitHub Token Setting Name',
      ],
    ];
  }

  /**
   * When the parameters are valid, the factory should return a client.
   *
   * @covers ::getClient
   */
  public function testGetClient() {
    $settings = new Settings([
      'key' => 'value',
    ]);
    $httpClientProphecy = $this->prophesize(HttpClient::class);
    $httpClient = $httpClientProphecy->reveal();
    $factory = new GitHubClientFactory($settings, $httpClient);
    $client = $factory->getClient('department-of-veterans-affairs/va.gov-cms', 'key');
    $this->assertInstanceOf(GitHubClientInterface::class, $client);
  }

}
