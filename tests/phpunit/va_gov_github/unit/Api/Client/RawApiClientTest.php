<?php

namespace Tests\va_gov_github\unit\Api\Client;

use Tests\Support\Traits\RawGitHubApiClientTrait;
use Tests\Support\Classes\VaGovUnitTestBase;
use Github\AuthMethod;

/**
 * Unit test of the API Client class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_github\Api\Client\ApiClient
 */
class RawApiClientTest extends VaGovUnitTestBase {

  use RawGitHubApiClientTrait;

  /**
   * Test that we can authenticate successfully.
   *
   * @param string $login
   *   The login.
   * @param string|null $password
   *   The password.
   * @param string $method
   *   The method.
   *
   * @dataProvider getAuthenticationData
   */
  public function testAuthenticate(string $login, string|null $password, string $method) {
    $builder = $this->getHttpClientBuilder($login, $password, $method);
    $client = $this->getRawApiClientWithBuilder($builder);
    $client->authenticate($login, $password, $method);
  }

  /**
   * Data provider for testAuthenticate().
   *
   * @dataProvider getAuthenticationData
   */
  public function getAuthenticationData(): array {
    return [
      ['token', NULL, AuthMethod::ACCESS_TOKEN],
      ['client_id', 'client_secret', AuthMethod::CLIENT_ID],
      ['token', NULL, AuthMethod::JWT],
    ];
  }

}
