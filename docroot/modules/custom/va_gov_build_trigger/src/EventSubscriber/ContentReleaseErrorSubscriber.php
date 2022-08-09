<?php

namespace Drupal\va_gov_build_trigger\EventSubscriber;

use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Service\BuildRequesterInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Re-queues a content release when an error condition happens.
 */
class ContentReleaseErrorSubscriber implements EventSubscriberInterface {

  /**
   * The build requester service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   */
  protected $buildRequester;

  /**
   * Constructor for ContentReleaseErrorSubscriber objects.
   */
  public function __construct(BuildRequesterInterface $buildRequester) {
    $this->buildRequester = $buildRequester;
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
  }

}
