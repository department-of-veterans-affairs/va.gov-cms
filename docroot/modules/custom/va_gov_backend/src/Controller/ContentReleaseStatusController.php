<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->dateFormatter = $container->get('date.formatter');
    $instance->httpClient = $container->get('http_client');
    $instance->renderer = $container->get('renderer');

    return $instance;
  }

  /**
   * Get last release status.
   *
   * @return string
   *   Return release status string.
   */
  public function getLastReleaseStatus() {
    $release_time = $this->getLastReleaseTime();

    $content = [
      '#type' => 'markup',
      '#markup' => $this->t('VA.gov last updated<br />@release_time', [
        '@release_time' => $release_time,
      ]),
    ];
    $output = $this->renderer->renderPlain($content);

    $response = Response::create($output);
    $response->setCache(['max_age' => 60]);

    return $response;
  }

  /**
   * Get the time of the last content release from va.gov.
   *
   * @return string
   *   Formatted date of last content release.
   */
  protected function getLastReleaseTime() {
    $timestamp = 0;

    /** @var \Psr\Http\Message\ResponseInterface $build_file */
    $build_file = $this->httpClient->request('GET', 'https://va.gov/BUILD.txt');

    $body = $build_file->getBody();
    if (preg_match('/BUILDTIME=([0-9]*)/', $body, $matches)) {
      $timestamp = $matches[1];
    }

    if ($timestamp) {
      $days = $this->getDaysAgo($timestamp);
      $time = $this->dateFormatter->format($timestamp, 'custom', 'h:i a T');
      return "{$days} at {$time}";
    }

    return '';
  }

  /**
   * Get the number of days from the passed timestamp until today.
   *
   * @param int $timestamp
   *   The UNIX timestamp to check.
   *
   * @return string
   *   Human-friendly number of days ago.
   */
  protected function getDaysAgo($timestamp) {
    $now = DrupalDateTime::createFromTimestamp(time())->setTime(0, 0, 0);
    $release_date = DrupalDateTime::createFromTimestamp($timestamp)->setTime(0, 0, 0);
    $difference_in_days = $now->diff($release_date)->d;

    if ($difference_in_days === 0) {
      return $this->t('today');
    }

    if ($difference_in_days === 1) {
      return $this->t('yesterday');
    }

    return $this->t('@days days ago', ['@days' => $difference_in_days]);
  }

}
