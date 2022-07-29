<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * {@inheritDoc}
 */
class ReleaseStateManager implements ReleaseStateManagerInterface {
  // These are the state keys that this class owns.
  protected const STATE_KEY = 'va_gov_build_trigger.release_state';
  protected const LAST_RELEASE_READY_KEY = 'va_gov_build_trigger.last_release_ready';
  protected const LAST_RELEASE_REQUEST_KEY = 'va_gov_build_trigger.last_release_requested';
  protected const LAST_RELEASE_DISPATCH_KEY = 'va_gov_build_trigger.last_release_dispatched';
  protected const LAST_RELEASE_STARTING_KEY = 'va_gov_build_trigger.last_release_starting';
  protected const LAST_RELEASE_INPROGRESS_KEY = 'va_gov_build_trigger.last_release_inprogress';
  protected const LAST_RELEASE_COMPLETE_KEY = 'va_gov_build_trigger.last_release_complete';

  // State transition actions.
  public const STATE_TRANSITION_SKIP = 'skip';
  public const STATE_TRANSITION_WAIT = 'wait';
  public const STATE_TRANSITION_OK = 'ok';
  public const STATE_TRANSITION_INVALID = 'invalid';

  // @todo Refactor to a PHP enum when we're on PHP 8.1.
  public const STATE_READY = 'ready';
  public const STATE_REQUESTED = 'requested';
  public const STATE_DISPATCHED = 'dispatched';
  public const STATE_STARTING = 'starting';
  public const STATE_INPROGRESS = 'inprogress';
  public const STATE_COMPLETE = 'complete';
  public const STATE_DEFAULT = self::STATE_READY;

  /**
   * Utility method to validate state transitions.
   *
   * @todo Update this to use an enum of states when we're on PHP 8.1
   *
   * @param string $release_state
   *   The release state param to validate.
   *
   * @return bool
   *   true if the provided state is a valid release state. otherwise, false.
   */
  public static function validateState(string $release_state) : bool {
    $valid_states = [
      self::STATE_READY,
      self::STATE_REQUESTED,
      self::STATE_DISPATCHED,
      self::STATE_STARTING,
      self::STATE_INPROGRESS,
      self::STATE_COMPLETE,
    ];

    return in_array($release_state, $valid_states);
  }

  /**
   * The state manager service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The event dispatcher service..
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

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
    $this->state = $state;
    $this->dispatcher = $dispatcher;
    $this->time = $time;
  }

  /**
   * {@inheritDoc}
   */
  public function getState() : string {
    return $this->state->get(self::STATE_KEY, self::STATE_DEFAULT);
  }

  /**
   * {@inheritDoc}
   */
  public function releaseIsImminent() : bool {
    $state = $this->getState();
    $release_imminent_states = [
      self::STATE_REQUESTED,
      self::STATE_DISPATCHED,
      self::STATE_STARTING,
    ];
    return in_array($state, $release_imminent_states);
  }

  /**
   * {@inheritDoc}
   */
  public function canAdvanceStateTo(string $proposed_state) : string {
    $this->requireValidState($proposed_state);

    $current_state = $this->getState();

    // We don't need to advance the state if the state that we'd advance to is
    // already the state that was advanced to the last time the state was
    // was advanced. :)
    if ($current_state === $proposed_state) {
      return self::STATE_TRANSITION_SKIP;
    }

    // This looks clunky, but with these vars, the conditions below are easier
    // to read and understand.
    $new_state_is_ready = ($proposed_state === self::STATE_READY);
    $new_state_is_requested = ($proposed_state === self::STATE_REQUESTED);
    $new_state_is_dispatched = ($proposed_state === self::STATE_DISPATCHED);
    $new_state_is_starting = ($proposed_state === self::STATE_STARTING);
    $new_state_is_inprogress = ($proposed_state === self::STATE_INPROGRESS);
    $new_state_is_complete = ($proposed_state === self::STATE_COMPLETE);
    $current_state_is_ready = ($current_state === self::STATE_READY);
    $current_state_is_requested = ($current_state === self::STATE_REQUESTED);
    $current_state_is_dispatched = ($current_state === self::STATE_DISPATCHED);
    $current_state_is_starting = ($current_state === self::STATE_STARTING);
    $current_state_is_inprogress = ($current_state === self::STATE_INPROGRESS);
    $current_state_is_complete = ($current_state === self::STATE_COMPLETE);

    // Advancing through the states in order is always okay.
    if ($current_state_is_complete && $new_state_is_ready) {
      return self::STATE_TRANSITION_OK;
    }
    if ($current_state_is_ready && $new_state_is_requested) {
      return self::STATE_TRANSITION_OK;
    }
    if ($current_state_is_requested && $new_state_is_dispatched) {
      return self::STATE_TRANSITION_OK;
    }
    if ($current_state_is_dispatched && $new_state_is_starting) {
      return self::STATE_TRANSITION_OK;
    }
    if ($current_state_is_starting && $new_state_is_inprogress) {
      return self::STATE_TRANSITION_OK;
    }
    if ($current_state_is_inprogress && $new_state_is_complete) {
      return self::STATE_TRANSITION_OK;
    }

    // If a new build is requested while the state is dispatched or starting,
    // the requested can be considered fulfilled by the build that's already in
    // progress (since the queries haven't started yet). In this case, a new
    // request doesn't need to be processed at all.
    $build_not_in_progress_yet = ($current_state_is_dispatched || $current_state_is_starting);
    if ($new_state_is_requested && $build_not_in_progress_yet) {
      return self::STATE_TRANSITION_SKIP;
    }

    // For all other transitions to requested, we should wait until next time.
    if ($new_state_is_requested) {
      return self::STATE_TRANSITION_WAIT;
    }

    // Other transitions are invalid.
    return self::STATE_TRANSITION_INVALID;
  }

  /**
   * {@inheritDoc}
   */
  public function advanceStateTo(string $new_state) : void {
    $this->requireValidState($new_state);

    if ($this->canAdvanceStateTo($new_state) !== self::STATE_TRANSITION_OK) {
      throw new \InvalidArgumentException('Cannot advance to provided state from current state');
    }

    $this->transitionState($new_state);
  }

  /**
   * {@inheritDoc}
   */
  public function resetState() : void {
    $this->transitionState(self::STATE_DEFAULT);
  }

  /**
   * {@inheritDoc}
   */
  public function transitionState(string $new_state) : void {
    $this->requireValidState($new_state);

    $old_state = $this->state->get(self::STATE_KEY, self::STATE_DEFAULT);
    $this->notifyListeners($old_state, $new_state);

    $this->updateReleaseState($new_state);
  }

  /**
   * Helper function to enforce valid state arguments.
   *
   * @param string $new_state
   *   The state to validate.
   */
  protected function requireValidState(string $new_state) : void {
    // Validate that the new state is acceptable.
    $new_state_is_valid = self::validateState($new_state);
    if (!$new_state_is_valid) {
      throw new \InvalidArgumentException('Invalid release state provided.');
    }
  }

  /**
   * Notify event listeners that the state has changed.
   *
   * @param string $old_state
   *   The content release state that is being transitioned from.
   * @param string $new_state
   *   The content release state that is being transitioned to.
   */
  protected function notifyListeners($old_state, $new_state) : void {
    $event = new ReleaseStateTransitionEvent($old_state, $new_state);
    // @phpstan-ignore-next-line
    $this->dispatcher->dispatch($event, ReleaseStateTransitionEvent::NAME);
  }

  /**
   * Update the state values.
   *
   * @param string $new_state
   *   The new release state to set in the site state service.
   */
  protected function updateReleaseState($new_state) : void {
    $this->state->set(self::STATE_KEY, $new_state);

    // Track timestamps for each state transition.
    $now = $this->time->getCurrentTime();
    switch ($new_state) {
      case self::STATE_READY:
        $this->state->set(self::LAST_RELEASE_READY_KEY, $now);
        break;

      case self::STATE_REQUESTED:
        $this->state->set(self::LAST_RELEASE_REQUEST_KEY, $now);
        break;

      case self::STATE_DISPATCHED:
        $this->state->set(self::LAST_RELEASE_DISPATCH_KEY, $now);
        break;

      case self::STATE_STARTING:
        $this->state->set(self::LAST_RELEASE_STARTING_KEY, $now);
        break;

      case self::STATE_INPROGRESS:
        $this->state->set(self::LAST_RELEASE_INPROGRESS_KEY, $now);
        break;

      case self::STATE_COMPLETE:
        $this->state->set(self::LAST_RELEASE_COMPLETE_KEY, $now);
        break;
    }
  }

}
