<?php

namespace Drupal\va_gov_build_trigger\EventSubscriber;

use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseAttemptsSinceLastSuccess;
use Drupal\va_gov_build_trigger\Service\BuildRequesterInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Re-queues a content release when an error condition happens.
 */
class ContentReleaseErrorSubscriber implements EventSubscriberInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The build requester service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   */
  protected $buildRequester;

  /**
   * Constructor for ContentReleaseErrorSubscriber objects.
   *
   * @param \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface $buildRequester
   *   The build requester service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(BuildRequesterInterface $buildRequester, StateInterface $state) {
    $this->buildRequester = $buildRequester;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ReleaseStateTransitionEvent::NAME] = 'handleError';
    return $events;
  }

  /**
   * Queue another build when an error condition is encountered.
   *
   * @param \Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent $event
   *   The release state transition event.
   */
  public function handleError(ReleaseStateTransitionEvent $event) {
    if ($event->getNewReleaseState() === ReleaseStateManager::STATE_ERROR) {
      $this->buildRequester->requestFrontendBuild('Retrying build after build failure.');
    }

    if ($event->getNewReleaseState() === ReleaseStateManager::STATE_DISPATCHED) {
      $attempts = $this->state->get(ContentReleaseAttemptsSinceLastSuccess::CONTENT_RELEASE_ATTEMPTS_STATE_KEY, 0);
      $this->state->set(ContentReleaseAttemptsSinceLastSuccess::CONTENT_RELEASE_ATTEMPTS_STATE_KEY, $attempts + 1);
    }

    if ($event->getNewReleaseState() === ReleaseStateManager::STATE_COMPLETE) {
      $this->state->set(ContentReleaseAttemptsSinceLastSuccess::CONTENT_RELEASE_ATTEMPTS_STATE_KEY, 0);
    }
  }

}
