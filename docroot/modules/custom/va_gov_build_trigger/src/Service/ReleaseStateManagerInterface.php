<?php

namespace Drupal\va_gov_build_trigger\Service;

/**
 * Describes the interface for the release state manager service.
 */
interface ReleaseStateManagerInterface {

  /**
   * Get the current release state.
   *
   * @return string
   *   The current release state.
   */
  public function getState() : string;

  /**
   * Determine if a release will be dispatched/run in the near future.
   *
   * @return bool
   *   Whether or not a release is coming up.
   */
  public function releaseIsImminent() : bool;

  /**
   * Update the release state to a new value.
   *
   * You should probably use advanceStateTo() instead. This method is used for
   * forcing a specific state.
   *
   * @param string $release_state
   *   The release state to transition to.
   *
   * @throws \InvalidArgumentException
   *   Thrown if the provided state is not a valid state.
   */
  public function transitionState(string $release_state) : void;

  /**
   * Determine if the proposed state is a valid next release state.
   *
   * @param string $proposed_state
   *   The release state that the caller wants to advance to.
   *
   * @return string
   *   One of the ReleaseStateManager::STATE_TRANSITION_* vars
   *
   * @throws \InvalidArgumentException
   *   Thrown if the provided state is not a valid state.
   */
  public function canAdvanceStateTo(string $proposed_state) : string;

  /**
   * Advance the state to the new state if possible.
   *
   * @param string $new_state
   *   The new state to advance to.
   *
   * @throws \InvalidArgumentException
   *   Thrown if the provided state is not a valid state.
   */
  public function advanceStateTo(string $new_state) : void;

  /**
   * Force the release state back to STATE_READY. Use this with care.
   */
  public function resetState() : void;

  /**
   * Record that a release status notification was received.
   */
  public function recordStatusNotification() : void;

  /**
   * Determine if the release state has gone stale.
   */
  public function releaseStateIsStale() : bool;

  /**
   * Get the last-release-complete timestamp.
   *
   * @return int
   *   The last-release-complete timestamp.
   */
  public function getLastReleaseCompleteTimestamp() : int;

  /**
   * Handle an error condition.
   */
  public function handleError() : void;

}
