<?php

namespace Tests\Support\Traits;

use Github\Client;
use Github\HttpClient\Builder;
use Github\HttpClient\Plugin\Authentication;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Http\Client\Common\HttpMethodsClientInterface;
use Psr\Http\Client\ClientInterface;

/**
 * A trait for mocking the API client.
 */
trait RawGitHubApiClientTrait {

  /**
   * Get authentication plugin.
   *
   * @param string $login
   *   The login.
   * @param string|null $password
   *   The password.
   * @param string $method
   *   The method.
   *
   * @return \Github\HttpClient\Plugin\Authentication
   *   The authentication plugin.
   */
  protected function getAuthenticationPlugin(string $login, string|null $password, string $method): Authentication {
    return new Authentication($login, $password, $method);
  }

  /**
   * Get authenticatable HTTP client builder.
   *
   * @param string $login
   *   The login.
   * @param string|null $password
   *   The password.
   * @param string $method
   *   The method.
   *
   * @return \Github\HttpClient\Builder
   *   The HTTP client builder.
   */
  public function getHttpClientBuilder(string $login, string|null $password, string $method): Builder {
    $plugin = $this->getAuthenticationPlugin($login, $password, $method);
    $builder = $this->getMockBuilder(Builder::class)
      ->setMethods(['addPlugin', 'removePlugin'])
      ->disableOriginalConstructor()
      ->getMock();
    $builder->expects($this->once())
      ->method('addPlugin')
      ->with($this->equalTo($plugin));
    $builder->expects($this->once())
      ->method('removePlugin')
      ->with(Authentication::class);
    return $builder;
  }

  /**
   * Get raw API client with specified HTTP client builder.
   *
   * @param \Github\HttpClient\Builder $builder
   *   The HTTP client builder.
   *
   * @return \Github\Client
   *   The raw API client.
   */
  public function getRawApiClientWithBuilder(Builder $builder): Client {
    $client = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->setMethods(['getHttpClientBuilder'])
      ->getMock();
    $client->expects($this->any())
      ->method('getHttpClientBuilder')
      ->willReturn($builder);
    return $client;
  }

  /**
   * Get raw API client with specified HTTP client.
   *
   * @param \Psr\Http\Client\ClientInterface $httpClient
   *   The HTTP client.
   *
   * @return \Github\Client
   *   The raw API client.
   */
  public function getRawApiClientWithHttpClient(ClientInterface $httpClient): Client {
    $client = $this->getMockBuilder(Client::class)
      ->disableOriginalConstructor()
      ->setMethods(['getHttpClient'])
      ->getMock();
    $client->expects($this->any())
      ->method('getHttpClient')
      ->willReturn($httpClient);
    return $client;
  }

  /**
   * Get an API mock for a specific API class.
   *
   * @param string $apiClass
   *   The API class.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   The mocked API client.
   */
  protected function getRawApiMock(string $apiClass) {
    $httpClient = $this->getMockBuilder(ClientInterface::class)
      ->setMethods(['sendRequest'])
      ->getMock();
    $httpClient
      ->expects($this->any())
      ->method('sendRequest');

    $client = Client::createWithHttpClient($httpClient);

    return $this->getMockBuilder($apiClass)
      ->setMethods(['get', 'post', 'postRaw', 'patch', 'delete', 'put', 'head'])
      ->setConstructorArgs([$client])
      ->getMock();
  }

  /**
   * Return a HttpMethods client mock.
   *
   * @return \Http\Client\Common\HttpMethodsClientInterface
   *   The mocked client.
   */
  protected function getHttpMethodsMock() {
    $mock = $this->createMock(HttpMethodsClientInterface::class);
    $mock->expects($this->any())->method('sendRequest');
    return $mock;
  }

  /**
   * Create a PSR-7 compliant response.
   *
   * @param array|string|null $value
   *   The expected object.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   The PSR7 response.
   */
  protected function getPsr7Response(array|string|null $value): Response {
    return new Response(
      200,
      ['Content-Type' => 'application/json'],
      stream_for(json_encode($value))
    );
  }

}
