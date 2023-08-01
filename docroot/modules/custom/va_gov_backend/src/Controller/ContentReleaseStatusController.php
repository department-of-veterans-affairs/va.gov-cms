<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets and formats the last production content release time.
 */
class ContentReleaseStatusController extends ControllerBase {

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Drupal Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Drupal timezone.
   *
   * @var string
   */
  protected $timezone;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->dateFormatter = $container->get('date.formatter');
    $instance->httpClient = $container->get('http_client');
    $instance->renderer = $container->get('renderer');
    $instance->time = $container->get('datetime.time');
    $instance->setStringTranslation($container->get('string_translation'));
    $instance->timezone = $container
      ->get('config.factory')
      ->get('system.date')
      ->get('timezone.default');
    return $instance;
  }

  /**
   * Get last release status response.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Return release status response.
   */
  public function getDefault(): Response {
    $renderArray = $this->getLastReleaseStatus();
    return $this->getLastReleaseResponse($renderArray);
  }

  /**
   * Get last release status render array.
   *
   * @param int|null $currentTimestamp
   *   The current time, or NULL to use the Drupal system time.
   *
   * @return array
   *   Render array for the last release status.
   */
  public function getLastReleaseStatus(?int $currentTimestamp = NULL): array {
    $releaseTimestamp = $this->getLastReleaseTimestamp();
    $currentTimestamp = $currentTimestamp ?? $this->time->getCurrentTime();
    $releaseMessage = $this->getLastReleaseMessage($releaseTimestamp, $currentTimestamp);
    return [
      '#type' => 'markup',
      '#markup' => $this->t('VA.gov last updated<br />@release_message', [
        '@release_message' => $releaseMessage,
      ]),
    ];
  }

  /**
   * Get last release status response.
   *
   * @param array $status
   *   The last release status render array.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response encapsulating the last release status message.
   */
  public function getLastReleaseResponse(array $status): Response {
    $output = $this->renderer->renderPlain($status);
    $response = Response::create($output);
    $response->setCache(['max_age' => 60]);
    return $response;
  }

  /**
   * Get the timestamp of the last content release from va.gov.
   *
   * @return int
   *   The timestamp parsed from the build file.
   *
   * @throws \Exception
   *   If the build file could not be parsed.
   */
  public function getLastReleaseTimestamp(): int {
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $response = $this->httpClient->get('https://www.va.gov/BUILD.txt');
    if (preg_match('/BUILDTIME=([0-9]*)/', $response->getBody(), $matches)) {
      return $matches[1];
    }
    throw new \Exception("Unable to parse timestamp from build file.");
  }

  /**
   * Calculate the last-released-at message.
   *
   * @param int $lastTimestamp
   *   The timestamp of the last release.
   * @param int $currentTimestamp
   *   The timestamp used as our basis for comparison.
   *
   * @return string
   *   Human-friendly message describing the last-release.
   */
  public function getLastReleaseMessage(int $lastTimestamp, int $currentTimestamp): string {
    $days = $this->getDifferenceInDaysMessage($lastTimestamp, $currentTimestamp);
    $time = $this->dateFormatter->format($lastTimestamp, 'custom', 'h:i a');
    return "{$days} at {$time}";
  }

  /**
   * Calculate the days portion of the last-released-at message.
   *
   * @param int $lastTimestamp
   *   The timestamp of the last release.
   * @param int $currentTimestamp
   *   The timestamp used as our basis for comparison.
   *
   * @return string
   *   Human-friendly message describing the last-release.
   */
  public function getDifferenceInDaysMessage(int $lastTimestamp, int $currentTimestamp): string {
    $differenceInDays = $this->getDifferenceInDays($lastTimestamp, $currentTimestamp);
    if ($differenceInDays === 0) {
      return $this->t('today');
    }
    if ($differenceInDays === 1) {
      return $this->t('yesterday');
    }
    return $this->t('@days days ago', [
      '@days' => $differenceInDays,
    ]);
  }

  /**
   * Calculate the difference in days between two timestamps.
   *
   * This is not a strict mathematical interval, e.g. ($2 - $1) / 86400.
   *
   * Rather, the timestamps are converted to dates in the Eastern timezone and
   * the calendar day difference is calculated.
   *
   * @param int $timestamp1
   *   One timestamp.
   * @param int $timestamp2
   *   A later timestamp.
   *
   * @return int
   *   The number of calendar days difference between the timestamps.
   */
  public function getDifferenceInDays(int $timestamp1, int $timestamp2): int {
    assert($timestamp2 >= $timestamp1);
    $timezone = new \DateTimeZone($this->timezone);
    $date1 = new \DateTime('@' . $timestamp1);
    $date1->setTimezone($timezone);
    $date1->setTime(0, 0, 0);
    $date2 = new \DateTime('@' . $timestamp2);
    $date2->setTimezone($timezone);
    $date2->setTime(0, 0, 0);
    $interval = $date1->diff($date2);
    return $interval->d;
  }

}
