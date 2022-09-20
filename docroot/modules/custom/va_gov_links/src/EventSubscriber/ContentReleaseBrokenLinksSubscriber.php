<?php

namespace Drupal\va_gov_links\EventSubscriber;

use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use GuzzleHttp\ClientInterface;

// Use GuzzleHttp\Exception\RequestException.

/**
 * Re-queues a content release when an error condition happens.
 */
class ContentReleaseBrokenLinksSubscriber implements EventSubscriberInterface {

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
   * Constructor for ContentReleaseBrokenLinksSubscriber objects.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Http client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(ClientInterface $http_client, StateInterface $state) {
    $this->httpClient = $http_client;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ReleaseStateTransitionEvent::NAME] = 'retrieveBrokenLinks';
    return $events;
  }

  /**
   * Queue another build when an error condition is encountered.
   *
   * @param \Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent $event
   *   The release state transition event.
   */
  public function retrieveBrokenLinks(ReleaseStateTransitionEvent $event) {
    if ($event->getNewReleaseState() === ReleaseStateManager::STATE_READY) {
      if (in_array($event->getOldReleaseState(), [
        ReleaseStateManager::STATE_COMPLETE,
        ReleaseStateManager::STATE_ERROR,
      ])) {
        try {

        }
        catch {

        }
        $response = $this->httpClient->request('GET', 'https://vetsgov-website-builds-s3-upload.s3-us-gov-west-1.amazonaws.com/broken-link-reports/vagovprod-broken-links.json');
        if ($response->getStatusCode() === 200) {
          $this->state->set('content_release.broken_links', $response->getBody()->getContents());
        }
      }
    }

  }

}
