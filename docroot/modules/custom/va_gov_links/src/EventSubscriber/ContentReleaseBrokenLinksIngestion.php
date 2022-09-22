<?php

namespace Drupal\va_gov_links\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Re-queues a content release when an error condition happens.
 */
class ContentReleaseBrokenLinksIngestion implements EventSubscriberInterface {

  /**
   * Http Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;
  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;
  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;
  /**
   * Settings Service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructor for ContentReleaseBrokenLinksSubscriber objects.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger logger logger logger.
   * @param \Drupal\Core\Site\Settings $settings
   *   Settings.
   */
  public function __construct(ClientInterface $http_client, StateInterface $state, LoggerChannelFactoryInterface $logger, Settings $settings) {
    $this->httpClient = $http_client;
    $this->state = $state;
    $this->logger = $logger;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ReleaseStateTransitionEvent::NAME] = 'retrieveBrokenLinks';
    return $events;
  }

  /**
   * Retrieve the broken links report when a content release finishes.
   *
   * @param \Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent $event
   *   The release state transition event.
   */
  public function retrieveBrokenLinks(ReleaseStateTransitionEvent $event) {
    if ($this->settings->get('broken_link_report_import_enabled')) {
      if ($event->getNewReleaseState() === ReleaseStateManager::STATE_READY) {
        if (in_array($event->getOldReleaseState(), [
          ReleaseStateManager::STATE_COMPLETE,
          ReleaseStateManager::STATE_ERROR,
        ])) {
          $report_location = $this->settings->get('broken_link_report_location');
          // If the report location starts with http, use an http client to
          // retrieve it. Otherwise, assume it is a local file and attempt to
          // read it.
          if (preg_match('/^http/', $report_location)) {
            try {
              $response = $this->httpClient->request('GET', $this->settings->get('broken_link_report_location'));
              if ($response->getStatusCode() === 200) {
                $this->state->set('content_release.broken_links', $response->getBody()->getContents());
                $this->logger->get('va_gov_links')->info('The broken links report was successfully retrieved and committed to state.');
              }
              else {
                $this->logger->get('va_gov_links')->error('Unable to retrieve broken links report; the request for ' . $report_location . ' returned a response code of ' . $response->getStatusCode());
              }
            }
            catch (RequestException $e) {
              $this->logger->get('va_gov_links')->error('HTTP client failed to retrieve ' . $report_location . '. Full error: ' . $e->getMessage());
            }
          }
          else {
            try {
              $file_path = $report_location;
              $file_stream = fopen($file_path, 'r');
              $contents = fread($file_stream, filesize($file_path));
              fclose($file_stream);
              if ($contents) {
                $this->state->set('content_release.broken_links', $contents);
                $this->logger->get('va_gov_links')->info('The broken links report was successfully read and committed to state.');
              }
              else {
                $this->logger->get('va_gov_links')->error('Unable to read ' . $report_location);
              }
            }
            catch (\Exception $e) {
              $this->logger->get('va_gov_links')->error('Unable to read ' . $report_location . '. Full error: ' . $e->getMessage());
            }
          }
        }
      }
    }
  }

}
