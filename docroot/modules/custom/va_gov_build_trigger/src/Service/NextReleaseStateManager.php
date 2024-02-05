<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A service for managing the state of the next release.
 */
class NextReleaseStateManager extends ReleaseStateManager {
  public const STATE_KEY = 'va_gov_build_trigger.next_release_state';

  /**
   * ReleaseStateManager constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The site state service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The site event dispatcher service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The site time service.
   */
  public function __construct(StateInterface $state, EventDispatcherInterface $dispatcher, TimeInterface $time) {
    parent::__construct($state, $dispatcher, $time);
  }

}
