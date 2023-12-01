<?php

namespace Drupal\va_gov_build_trigger\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseInterval;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Records the interval between content releases.
 */
class ContentReleaseIntervalSubscriber implements EventSubscriberInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new ContentReleaseIntervalSubscriber object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(StateInterface $state, TimeInterface $time) {
    $this->state = $state;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ReleaseStateTransitionEvent::NAME] = 'recordDeployInterval';
    return $events;
  }

  /**
   * Record the amount of time since the last content release completed.
   *
   * @param \Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent $event
   *   The release state transition event.
   */
  public function recordDeployInterval(ReleaseStateTransitionEvent $event) {
    if ($event->getNewReleaseState() === ReleaseStateManager::STATE_COMPLETE) {
      $now = $this->time->getCurrentTime();
      $last_complete_time = $this->state->get(ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY, $now);
      $interval = $now - $last_complete_time;

      $this->state->set(ContentReleaseInterval::CONTENT_RELEASE_INTERVAL_STATE_KEY, $interval);
    }
  }

}
