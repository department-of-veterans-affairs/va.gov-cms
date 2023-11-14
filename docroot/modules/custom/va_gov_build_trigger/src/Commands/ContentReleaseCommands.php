<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Controller\ContentReleaseNotificationController;
use Drupal\va_gov_build_trigger\EventSubscriber\ContinuousReleaseSubscriber;
use Drupal\va_gov_build_trigger\Service\BuildSchedulerInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush interface to the content release.
 */
class ContentReleaseCommands extends DrushCommands {
  /**
   * The release state manager.
   *
   * @var \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface
   */
  protected $releaseStateManager;

  /**
   * The build scheduler service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildSchedulerInterface
   */
  protected $buildScheduler;

  /**
   * The content release request service.
   *
   * @var \Drupal\va_gov_content_release\Request\RequestInterface
   */
  protected $requestService;

  /**
   * The state management service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   The release state manager service.
   * @param \Drupal\va_gov_build_trigger\Service\BuildSchedulerInterface $buildScheduler
   *   The build scheduler service.
   * @param \Drupal\va_gov_content_release\Request\RequestInterface $requestService
   *   The request service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   A logger channel factory.
   */
  public function __construct(
    ReleaseStateManagerInterface $releaseStateManager,
    BuildSchedulerInterface $buildScheduler,
    RequestInterface $requestService,
    StateInterface $state,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    $this->releaseStateManager = $releaseStateManager;
    $this->buildScheduler = $buildScheduler;
    $this->requestService = $requestService;
    $this->state = $state;
    $this->logger = $loggerChannelFactory->get('va_gov_build_trigger');
  }

  /**
   * Reset the content release state.
   *
   * @command va-gov:content-release:reset-state
   * @aliases va-gov-content-release-reset-state
   */
  public function resetState() {
    $this->releaseStateManager->resetState();
    $this->logger()->info('Content release state has been reset to \'ready\'.');
  }

  /**
   * Advance the state like an external system would do through HTTP.
   *
   * @param string $state
   *   Required. Declare which state to advance to.
   *
   * @command va-gov:content-release:advance-state
   * @aliases va-gov-content-release-advance-state
   */
  public function advanceState($state) {
    $is_allowed_notification = in_array($state, ContentReleaseNotificationController::allowedNotifications());
    $can_transition = $this->releaseStateManager->canAdvanceStateTo($state);
    $can_transition = ($can_transition === ReleaseStateManager::STATE_TRANSITION_OK);

    if (!$is_allowed_notification || !$can_transition) {
      $this->logger()->error('State cannot be advanced to @state', [
        '@state' => $state,
      ]);
      return;
    }

    $this->releaseStateManager->advanceStateTo($state);
    $this->logger()->info('State has been advanced to @state', [
      '@state' => $state,
    ]);
  }

  /**
   * Get the current release state.
   *
   * @command va-gov:content-release:get-state
   * @aliases va-gov-content-release-get-state
   */
  public function getReleaseState() {
    $state = $this->releaseStateManager->getState();
    $this->io()->write($state);
  }

  /**
   * Make sure builds are going out at least hourly during business hours.
   *
   * @command va-gov:content-release:check-scheduled
   * @aliases va-gov-content-release-check-scheduled
   */
  public function checkScheduledBuild() {
    $this->buildScheduler->checkScheduledBuild();
  }

  /**
   * If the state is stale, reset the state.
   *
   * @command va-gov:content-release:check-stale
   * @aliases va-gov-content-release-check-stale
   */
  public function checkStale() {
    if ($this->releaseStateManager->releaseStateIsStale()) {
      $this->resetState();
      $this->requestService->submitRequest('Submitting new request due to staleness.');
    }
  }

  /**
   * Check continuous release state.
   *
   * @command va-gov:content-release:is-continuous-release-enabled
   * @aliases va-gov-content-release-is-continuous-release-enabled
   */
  public function checkContinuousReleaseState() {
    $this->io()->writeln(print_r($this->state->get(ContinuousReleaseSubscriber::CONTINUOUS_RELEASE_ENABLED, FALSE)));
  }

  /**
   * Toggle continuous release.
   *
   * @command va-gov:content-release:toggle-continuous
   * @aliases va-gov-content-release-toggle-continuous
   */
  public function toggleContinuousRelease() {
    $current = $this->state->get(ContinuousReleaseSubscriber::CONTINUOUS_RELEASE_ENABLED, FALSE);
    $this->state->set(ContinuousReleaseSubscriber::CONTINUOUS_RELEASE_ENABLED, !$current);
    $status_text = (!$current === TRUE ? 'enabled' : 'disabled');
    $this->io()->writeln('Continuous release is now ' . $status_text);
  }

}
