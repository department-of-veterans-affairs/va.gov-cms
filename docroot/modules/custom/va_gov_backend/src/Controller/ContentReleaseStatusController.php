<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentReleaseStatusController.
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->httpClient = $container->get('http_client');
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
      '#markup' => $this->t('Content last released to VA.gov<br />@release_time', [
        '@release_time' => $release_time,
      ]),
    ];
    $output = render($content);

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

    /** @var $build_file \Psr\Http\Message\ResponseInterface */
    $build_file = $this->httpClient->request('GET', 'https://va.gov/BUILD.txt');

    $body = $build_file->getBody();
    if (preg_match('/BUILDTIME=([0-9]*)/', $body, $matches)) {
      $timestamp = $matches[1];
    }

    if ($timestamp) {
      return $this->dateFormatter->format($timestamp, 'standard');
    }

    return '';
  }

}
