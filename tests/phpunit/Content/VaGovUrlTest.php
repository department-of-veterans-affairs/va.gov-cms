<?php

namespace tests\phpunit\Content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\va_gov_backend\Service\VaGovUrl;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use GuzzleHttp\ClientInterface;
use Prophecy\Argument;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * A test to confirm the proper functioning of the VaGovUrl class.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Service\VaGovUrl
 */
class VaGovUrlTest extends VaGovUnitTestBase {

  /**
   * Provide a Settings object.
   */
  public function getSettings(array $array = []) {
    return new Settings($array);
  }

  /**
   * Provide a ClientInterface prophecy.
   */
  public function getClientInterfaceProphecy() {
    return $this->prophesize(ClientInterface::CLASS);
  }

  /**
   * Provide a ClientInterface prophecy.
   */
  public function getEnvironmentDiscoveryProphecy() {
    return $this->prophesize(EnvironmentDiscovery::CLASS);
  }

  /**
   * Provide an EntityInterface prophecy.
   */
  public function getEntityInterfaceProphecy($nid) {
    $entityProphecy = $this->prophesize(EntityInterface::CLASS);

    // ::toUrl().
    $entityUrlProphecy = $this->prophesize(Url::CLASS);
    $entityUrlProphecy
      ->toString()
      ->willReturn("/node/$nid");
    $entityUrl = $entityUrlProphecy->reveal();
    $entityProphecy
      ->toUrl()
      ->shouldBeCalled()
      ->willReturn($entityUrl);

    return $entityProphecy;
  }

  /**
   * Provide a VaGovUrl object constructed with the specified services.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client.
   * @param \Drupal\Core\Site\Settings $settings
   *   The read-only settings container.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   The environment Discovery service.
   */
  public function getVaGovUrl(ClientInterface $httpClient = NULL, Settings $settings = NULL, EnvironmentDiscovery $environmentDiscovery = NULL) {
    $httpClient = $httpClient ?? $this->getClientInterfaceProphecy()->reveal();
    $settings = $settings ?? $this->getSettings();
    $environmentDiscovery = $environmentDiscovery ?? $this->getEnvironmentDiscoveryProphecy()->reveal();
    return new VaGovUrl($httpClient, $settings, $environmentDiscovery);
  }

  /**
   * Test ::getVaGovFrontEndUrl().
   *
   * @covers ::getVaGovFrontEndUrl
   */
  public function testGetVaGovFrontEndUrl() {
    $environmentDiscoveryProphecy = $this->getEnvironmentDiscoveryProphecy();
    $expectedUrl = 'https://www.va.gov';
    $environmentDiscoveryProphecy
      ->getWebUrl()
      ->shouldBeCalled()
      ->willReturn($expectedUrl);
    $vaGovUrl = $this->getVaGovUrl(NULL, NULL, $environmentDiscoveryProphecy->reveal());
    $this->assertEquals($expectedUrl, $vaGovUrl->getVaGovFrontEndUrl());
  }

  /**
   * Test ::getVaGovFrontEndUrlForEntity().
   *
   * @covers ::getVaGovFrontEndUrlForEntity
   */
  public function testGetVaGovFrontEndUrlForEntity() {
    $environmentDiscoveryProphecy = $this->getEnvironmentDiscoveryProphecy();
    $expectedUrl = 'https://www.va.gov';
    $environmentDiscoveryProphecy
      ->getWebUrl()
      ->shouldBeCalled()
      ->willReturn($expectedUrl);
    $vaGovUrl = $this->getVaGovUrl(NULL, NULL, $environmentDiscoveryProphecy->reveal());
    $nid = 12412;
    $entityProphecy = $this->getEntityInterfaceProphecy($nid);
    $expectedEntityUrl = "$expectedUrl/node/$nid";
    $this->assertEquals($expectedEntityUrl, $vaGovUrl->getVaGovFrontEndUrlForEntity($entityProphecy->reveal()));
  }

  /**
   * Test ::vaGovFrontEndUrlForEntityIsLive().
   *
   * @covers ::vaGovFrontEndUrlForEntityIsLive
   */
  public function testVaGovFrontEndUrlForEntityIsLive() {
    $environmentDiscoveryProphecy = $this->getEnvironmentDiscoveryProphecy();
    $expectedUrl = 'https://www.va.gov';
    $environmentDiscoveryProphecy
      ->getWebUrl()
      ->shouldBeCalled()
      ->willReturn($expectedUrl);
    $httpClientProphecy = $this->getClientInterfaceProphecy();
    $httpClientProphecy
      ->request(Argument::type('string'), Argument::type('string'), Argument::type('array'))
      ->willReturn([]);
    $httpClient = $httpClientProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $vaGovUrl = $this->getVaGovUrl($httpClient, NULL, $environmentDiscovery);
    $nid = 12412;
    $entityProphecy = $this->getEntityInterfaceProphecy($nid);
    $this->assertEquals(TRUE, $vaGovUrl->vaGovFrontEndUrlForEntityIsLive($entityProphecy->reveal()));
  }

  /**
   * Test ::vaGovFrontEndUrlForEntityIsLive().
   *
   * @covers ::vaGovFrontEndUrlForEntityIsLive
   */
  public function testVaGovFrontEndUrlForEntityIsLive2() {
    $environmentDiscoveryProphecy = $this->getEnvironmentDiscoveryProphecy();
    $expectedUrl = 'https://www.va.gov';
    $environmentDiscoveryProphecy
      ->getWebUrl()
      ->shouldBeCalled()
      ->willReturn($expectedUrl);
    $httpClientProphecy = $this->getClientInterfaceProphecy();
    $httpClientProphecy
      ->request(Argument::type('string'), Argument::type('string'), Argument::type('array'))
      ->willThrow(\Exception::CLASS);
    $httpClient = $httpClientProphecy->reveal();
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $vaGovUrl = $this->getVaGovUrl($httpClient, NULL, $environmentDiscovery);
    $nid = 12412;
    $entityProphecy = $this->getEntityInterfaceProphecy($nid);
    $this->assertEquals(FALSE, $vaGovUrl->vaGovFrontEndUrlForEntityIsLive($entityProphecy->reveal()));
  }

}
