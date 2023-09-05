<?php

namespace tests\phpunit\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_backend\Controller\ContentReleaseStatusController;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Provides automated tests for the ContentReleaseStatusController controller.
 *
 * @group unit
 * @group all
 */
class ContentReleaseStatusControllerTest extends VaGovUnitTestBase {

  /**
   * Generate a response body matching va.gov/BUILD.txt.
   *
   * @param int $timestamp
   *   A Unix timestamp.
   *
   * @return string
   *   Response body text.
   */
  protected function getBuildFileForTimestamp(int $timestamp) : string {
    return "BUILDTYPE=vagovprod\nNODE_ENV=production\nBRANCH_NAME=null\nCHANGE_TARGET=null\nBUILD_ID=799\nBUILD_NUMBER=799\nREF=80ff0a2438f1a0d038ab8637f553a83792596bd4\nBUILDTIME={$timestamp}";
  }

  /**
   * Generate a timestamp with a relative portion, timestamp, and timezone.
   *
   * @param string $relative
   *   Relative portion.
   * @param int $baseTimestamp
   *   Base timestamp.
   * @param string $timezone
   *   Timezone.
   *
   * @return int
   *   An adjusted timestamp.
   */
  protected function getTimestamp(string $relative, int $baseTimestamp, string $timezone = 'America/New_York'): int {
    $timezone = new \DateTimeZone($timezone);
    $datetime = new \DateTime('@' . $baseTimestamp);
    $datetime->setTimezone($timezone);
    $datetime->modify($relative);
    return $datetime->getTimestamp();
  }

  /**
   * Format a timestamp with a timezone.
   *
   * @param int $timestamp
   *   Base timestamp.
   * @param string $format
   *   Format string.
   * @param string $timezone
   *   Timezone.
   *
   * @return string
   *   The formatted date.
   */
  protected function formatTimestamp(int $timestamp, string $format, string $timezone = 'America/New_York'): string {
    $timezone = new \DateTimeZone($timezone);
    $datetime = new \DateTime('@' . $timestamp);
    $datetime->setTimezone($timezone);
    return $datetime->format($format);
  }

  /**
   * Mock the http client.
   *
   * @param string $responseBody
   *   The desired response body.
   *
   * @return \GuzzleHttp\ClientInterface
   *   Guzzle client.
   */
  protected function getMockHttpClient(string $responseBody): ClientInterface {
    $prophecy = $this->prophesize(Client::CLASS);

    $responseProphecy = $this->prophesize(Response::CLASS);
    $responseProphecy->getBody()->willReturn(Utils::streamFor($responseBody));

    $prophecy->get(Argument::type('string'))->willReturn($responseProphecy->reveal());

    return $prophecy->reveal();
  }

  /**
   * Tests content release status controller.
   *
   * @dataProvider dataProviderContentReleaseStatusController
   */
  public function testContentReleaseStatusController(
    int $lastTimestamp,
    int $currentTimestamp,
    int $expectedDays,
    string $expectedStatus,
    string $timezone = 'America/New_York'
  ) : void {
    $buildFile = $this->getBuildFileForTimestamp($lastTimestamp);

    $container = new ContainerBuilder();
    $dateFormatterProphecy = $this->prophesize(DateFormatter::CLASS);
    $dateFormatterProphecy
      ->format(Argument::type('int'), Argument::type('string'), Argument::type('string'))
      ->will(function ($args) use ($timezone) {
        $baseTimestamp = $args[0];
        $format = $args[2];
        $datetime = new \DateTime('@' . $baseTimestamp);
        $datetime->setTimezone(new \DateTimeZone($timezone));
        return $datetime->format($format);
      });
    $container->set('date.formatter', $dateFormatterProphecy->reveal());
    $container->set('http_client', $this->getMockHttpClient($buildFile));
    $rendererProphecy = $this->prophesize(Renderer::CLASS);
    $rendererProphecy
      ->renderPlain(Argument::type('array'))
      ->will(function ($args) {
        return $args[0]['#markup'];
      });
    $container->set('renderer', $rendererProphecy->reveal());
    $container->set('datetime.time', $this->prophesize(Renderer::CLASS)->reveal());
    $translationProphecy = $this->prophesize(TranslationInterface::CLASS);
    $translationProphecy
      ->translateString(Argument::any())
      ->will(function ($args) {
        return $args[0]->getUntranslatedString();
      });
    $container->set('string_translation', $translationProphecy->reveal());
    $systemDateConfigProphecy = $this->prophesize(ImmutableConfig::CLASS);
    $systemDateConfigProphecy
      ->get(Argument::type('string'))
      ->willReturn('America/New_York');
    $configFactoryProphecy = $this->prophesize(ConfigFactoryInterface::CLASS);
    $configFactoryProphecy
      ->get(Argument::type('string'))
      ->willReturn($systemDateConfigProphecy->reveal());
    $container->set('config.factory', $configFactoryProphecy->reveal());
    $controller = ContentReleaseStatusController::create($container);

    $actualDays = $controller->getDifferenceInDays($lastTimestamp, $currentTimestamp);
    $this->assertEquals($expectedDays, $actualDays);
    $actualStatus = $controller->getLastReleaseStatus($currentTimestamp);
    $actualContent = $controller->getLastReleaseResponse($actualStatus)->getContent();
    $this->assertEquals($expectedStatus, $actualStatus['#markup']);
    $this->assertEquals($actualStatus['#markup'], $actualContent);
  }

  /**
   * Data provider for testContentReleaseStatusController.
   *
   * @return array
   *   Test assertion data.
   */
  public function dataProviderContentReleaseStatusController() : array {
    $time = time();
    return [
      [
        $this->getTimestamp('today', $time),
        $this->getTimestamp('today', $time),
        0,
        'VA.gov last updated<br />today at 12:00 am',
      ],
      [
        $this->getTimestamp('today 12am', $time),
        $time,
        0,
        'VA.gov last updated<br />today at 12:00 am',
      ],
      [
        $time,
        $time,
        0,
        'VA.gov last updated<br />today at ' . $this->formatTimestamp($time, 'h:i a'),
      ],
      [
        $this->getTimestamp('today 12am', $time) - 5,
        $time,
        1,
        'VA.gov last updated<br />yesterday at 11:59 pm',
      ],
      [
        $this->getTimestamp('yesterday 12am', $time),
        $this->getTimestamp('yesterday 12am', $time),
        0,
        'VA.gov last updated<br />today at 12:00 am',
      ],
      [
        $this->getTimestamp('yesterday 12am', $time),
        $this->getTimestamp('today 12am', $time),
        1,
        'VA.gov last updated<br />yesterday at 12:00 am',
      ],
      [
        $this->getTimestamp('yesterday 12am', $time),
        $time,
        1,
        'VA.gov last updated<br />yesterday at 12:00 am',
      ],
      [
        $this->getTimestamp('yesterday 1405', $time),
        $time,
        1,
        'VA.gov last updated<br />yesterday at 02:05 pm',
      ],
      [
        $this->getTimestamp('-1 day 12am', $time),
        $this->getTimestamp('yesterday 12am', $time),
        0,
        'VA.gov last updated<br />today at 12:00 am',
      ],
      [
        $this->getTimestamp('-1 day 12am', $time),
        $this->getTimestamp('-1 day 12am', $time),
        0,
        'VA.gov last updated<br />today at 12:00 am',
      ],
      [
        $this->getTimestamp('-1 day 12am', $time),
        $time,
        1,
        'VA.gov last updated<br />yesterday at 12:00 am',
      ],
      [
        $this->getTimestamp('-1 day 12am', $time),
        $this->getTimestamp('today 12am', $time),
        1,
        'VA.gov last updated<br />yesterday at 12:00 am',
      ],
      [
        $this->getTimestamp('-2 day 12am', $time),
        $this->getTimestamp('today 12am', $time),
        2,
        'VA.gov last updated<br />2 days ago at 12:00 am',
      ],
      [
        $this->getTimestamp('-2 day 12am', $time),
        $time,
        2,
        'VA.gov last updated<br />2 days ago at 12:00 am',
      ],
      [
        $this->getTimestamp('-3 day 12am', $time),
        $this->getTimestamp('today 12am', $time),
        3,
        'VA.gov last updated<br />3 days ago at 12:00 am',
      ],
      [
        $this->getTimestamp('-3 day 12am', $time),
        $time,
        3,
        'VA.gov last updated<br />3 days ago at 12:00 am',
      ],
      [
        $this->getTimestamp('-4 day 12am', $time),
        $this->getTimestamp('today 12am', $time),
        4,
        'VA.gov last updated<br />4 days ago at 12:00 am',
      ],
      [
        $this->getTimestamp('-4 day 12am', $time),
        $time,
        4,
        'VA.gov last updated<br />4 days ago at 12:00 am',
      ],
      [
        $this->getTimestamp('-4 day 1038', $time),
        $time,
        4,
        'VA.gov last updated<br />4 days ago at 10:38 am',
      ],
    ];
  }

}
