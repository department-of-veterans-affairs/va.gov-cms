<?php

namespace Drupal\va_gov_build_trigger\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;

/**
 * This event is dispatched just before the release state is changed.
 */
class ReleaseStateTransitionEvent extends Event {
  public const NAME = 'release_state.transition';

  /**
   * The old release state.
   *
   * This should be one of the ReleaseStateManager::STATE_* constants.
   *
   * @var string
   */
  protected $oldReleaseState;

  /**
   * The new release state.
   *
   * This should be one of the ReleaseStateManager::STATE_* constants.
   *
   * @var string
   */
  protected $newReleaseState;

  /**
   * ReleaseStateTransitionEvent constructor.
   *
   * @param string $oldReleaseState
   *   The release state being transitioned from.
   * @param string $newReleaseState
   *   The release state being transitioned to.
   */
  public function __construct(string $oldReleaseState, string $newReleaseState) {
    // Validate the the old and new states are among the enumerated states in
    // ReleaseStateManager.
    $old_state_is_valid = ReleaseStateManager::validateState($oldReleaseState);
    $new_state_is_valid = ReleaseStateManager::validateState($newReleaseState);
    if (!$old_state_is_valid || !$new_state_is_valid) {
      throw new \InvalidArgumentException('Invalid release state provided.');
    }

    $this->oldReleaseState = $oldReleaseState;
    $this->newReleaseState = $newReleaseState;
  }

  /**
   * Get the old release state.
   *
   * @see ReleaseStateManager
   *
   * @return string
   *   The old release state.
   */
  public function getOldReleaseState() : string {
    return $this->oldReleaseState;
  }

  /**
   * Get the new release state.
   *
   * @see ReleaseStateManager
   *
   * @return string
   *   The new release state.
   */
  public function getNewReleaseState() : string {
    return $this->newReleaseState;
  }

}
