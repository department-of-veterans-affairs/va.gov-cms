<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Http\RequestStack;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Tests\Support\Mock\SpecifiedTime;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Unit test for the release state manager.
 */
class ReleaseStateManagerTest extends UnitTestCase {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

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
   * A date used for testing (January 1, 2022 00:00:00).
   *
   * @var int
   */
  protected const TIMESTAMP = 1641020400;

  /**
   * A list of valid release states.
   *
   * @var string[]
   */
  protected $validReleaseStates = [
    ReleaseStateManager::STATE_READY,
    ReleaseStateManager::STATE_REQUESTED,
    ReleaseStateManager::STATE_DISPATCHED,
    ReleaseStateManager::STATE_STARTING,
    ReleaseStateManager::STATE_INPROGRESS,
    ReleaseStateManager::STATE_COMPLETE,
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->state = new State(new KeyValueMemoryFactory());
    $this->eventDispatcher = new EventDispatcher();
    $this->time = new SpecifiedTime(new RequestStack());
    $this->time->setCurrentTime(self::TIMESTAMP);
  }

  /**
   * Test that we're getting state values back from getState().
   */
  public function testGetState() {
    $rsm = new ReleaseStateManager($this->state, $this->eventDispatcher, $this->time);
    $this->assertEquals(ReleaseStateManager::STATE_DEFAULT, $rsm->getState());

    foreach ($this->validReleaseStates as $state) {
      $this->state->set('va_gov_build_trigger.release_state', $state);
      $rsm = new ReleaseStateManager($this->state, $this->eventDispatcher, $this->time);
      $this->assertEquals($state, $rsm->getState());
    }
  }

  /**
   * Test the behavior of canAdvanceStateTo().
   */
  public function testCanAdvanceStateTo() {
    $fromStates = $this->validReleaseStates;
    $toStates = $this->validReleaseStates;
    $allowedStateTransitions = $this->allowedStateTransitions();

    foreach ($fromStates as $fromState) {
      foreach ($toStates as $toState) {
        $this->state->set('va_gov_build_trigger.release_state', $fromState);
        $rsm = new ReleaseStateManager($this->state, $this->eventDispatcher, $this->time);
        if (isset($allowedStateTransitions[$fromState][$toState])) {
          $this->assertEquals($allowedStateTransitions[$fromState][$toState], $rsm->canAdvanceStateTo($toState));
        }
        else {
          $this->assertEquals(ReleaseStateManager::STATE_TRANSITION_INVALID, $rsm->canAdvanceStateTo($toState));
        }
      }
    }

    $this->expectException(\InvalidArgumentException::class);
    $rsm->canAdvanceStateTo('notarealstate');
  }

  /**
   * Test that providing an invalid state to advanceStateTo throws an exception.
   *
   * This is the only thing that needs tested separately in advanceStateTo().
   * The rest of the functionality is tested in canAdvanceStateTo and
   * transitionState.
   */
  public function testAdvanceStateToInvalidState() {
    $rsm = new ReleaseStateManager($this->state, $this->eventDispatcher, $this->time);

    $this->expectException(\InvalidArgumentException::class);
    $rsm->advanceStateTo('notarealstate');
  }

  /**
   * Tests the transitionState method.
   *
   * For each state transition, test that the following conditions are true:
   *   - Listeners are notified properly
   *   - The release state is updated
   *   - The appropriate timestamp is updated.
   */
  public function testTransitionState() {
    $fromStates = $this->validReleaseStates;
    $toStates = $this->validReleaseStates;

    foreach ($fromStates as $fromState) {
      foreach ($toStates as $toState) {
        $this->state->set('va_gov_build_trigger.release_state', $fromState);

        // Set up an event listener to make sure that it was called properly.
        $called = FALSE;
        $listener = $this->getEventListenerTestingCallback($fromState, $toState, $called);
        $this->eventDispatcher->addListener(
          ReleaseStateTransitionEvent::NAME,
          $listener
        );

        $rsm = new ReleaseStateManager($this->state, $this->eventDispatcher, $this->time);

        $rsm->transitionState($toState);
        $this->assertTrue($called, 'Event listener was called');
        $this->assertEquals($toState, $this->state->get('va_gov_build_trigger.release_state'));
        $this->assertEquals(self::TIMESTAMP, $this->state->get('va_gov_build_trigger.last_release_' . $toState));

        // Clean up so we don't accumulate event listeners.
        $this->eventDispatcher->removeListener(ReleaseStateTransitionEvent::NAME, $listener);
      }
    }

    $this->expectException(\InvalidArgumentException::class);
    $rsm->transitionstate('notarealstate');
  }

  /**
   * Test resetState from every other state.
   */
  public function testResetState() {
    $fromStates = $this->validReleaseStates;

    foreach ($fromStates as $fromState) {
      $this->state->set('va_gov_build_trigger.release_state', $fromState);
      // Set up an event listener to make sure that it was called properly.
      $called = FALSE;
      $listener = $this->getEventListenerTestingCallback($fromState, ReleaseStateManager::STATE_DEFAULT, $called);
      $this->eventDispatcher->addListener(
        ReleaseStateTransitionEvent::NAME,
        $listener
      );
      $rsm = new ReleaseStateManager($this->state, $this->eventDispatcher, $this->time);

      $rsm->resetState();
      $this->assertTrue($called, 'Event listener was called');
      $this->assertEquals(ReleaseStateManager::STATE_DEFAULT, $this->state->get('va_gov_build_trigger.release_state'));
      $this->assertEquals(self::TIMESTAMP, $this->state->get('va_gov_build_trigger.last_release_' . ReleaseStateManager::STATE_DEFAULT));

      // Clean up so we don't accumulate event listeners.
      $this->eventDispatcher->removeListener(ReleaseStateTransitionEvent::NAME, $listener);
    }

  }

  /**
   * Get a observable callback for the event responder.
   *
   * @param string $from_state
   *   The state that should be transitioned from.
   * @param string $to_state
   *   The state that should be transitioned to.
   * @param bool $called
   *   Used for observing whether or not the event was dispatched properly.
   *
   * @return \Closure
   *   The function used for the test.
   */
  protected function getEventListenerTestingCallback($from_state, $to_state, &$called) {
    return function (ReleaseStateTransitionEvent $event) use ($from_state, $to_state, &$called) {
      $called = TRUE;
      $this->assertEquals($from_state, $event->getOldReleaseState());
      $this->assertEquals($to_state, $event->getNewReleaseState());
    };
  }

  /**
   * Allowed transitions for testCanAdvanceStateTo().
   */
  protected function allowedStateTransitions() {
    return [
      ReleaseStateManager::STATE_READY => [
        ReleaseStateManager::STATE_READY => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_REQUESTED => ReleaseStateManager::STATE_TRANSITION_OK,
      ],
      ReleaseStateManager::STATE_REQUESTED => [
        ReleaseStateManager::STATE_REQUESTED => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_DISPATCHED => ReleaseStateManager::STATE_TRANSITION_OK,
      ],
      ReleaseStateManager::STATE_DISPATCHED => [
        ReleaseStateManager::STATE_REQUESTED => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_DISPATCHED => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_STARTING => ReleaseStateManager::STATE_TRANSITION_OK,
      ],
      ReleaseStateManager::STATE_STARTING => [
        ReleaseStateManager::STATE_REQUESTED => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_STARTING => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_INPROGRESS => ReleaseStateManager::STATE_TRANSITION_OK,
      ],
      ReleaseStateManager::STATE_INPROGRESS => [
        ReleaseStateManager::STATE_REQUESTED => ReleaseStateManager::STATE_TRANSITION_WAIT,
        ReleaseStateManager::STATE_INPROGRESS => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_COMPLETE => ReleaseStateManager::STATE_TRANSITION_OK,
      ],
      ReleaseStateManager::STATE_COMPLETE => [
        ReleaseStateManager::STATE_REQUESTED => ReleaseStateManager::STATE_TRANSITION_WAIT,
        ReleaseStateManager::STATE_COMPLETE => ReleaseStateManager::STATE_TRANSITION_SKIP,
        ReleaseStateManager::STATE_READY => ReleaseStateManager::STATE_TRANSITION_OK,
      ],
    ];
  }

}
