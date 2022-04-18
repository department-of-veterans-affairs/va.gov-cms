<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Controller\ContentReleaseNotificationController;
use Drupal\va_gov_build_trigger\Service\BuildRequester;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
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
   * The state management service.
   *
   * @var StateInterface
   */
  protected $state;

  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   The release state manager service.
   * @param StateInterface $state
   *   The state service.
   * @param \Psr\Log\LoggerChannelFactoryInterface $logger
   *   A logger channel factory
   */
  public function __construct(
    ReleaseStateManagerInterface $releaseStateManager,
    StateInterface $state,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    $this->releaseStateManager = $releaseStateManager;
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
   * Reset the content release frontend version.
   *
   * @command va-gov:content-release:reset-frontend-version
   * @aliases va-gov-content-release-reset-frontend-version
   */
  public function resetFrontendVersion() {
    $this->state->delete(BuildRequester::VA_GOV_FRONTEND_VERSION);
    $this->logger()->info('Content release state has been reset to @state.', [
      '@state' => ReleaseStateManager::STATE_DEFAULT,
    ]);
  }

  /**
   * Advance the state like an external system would do through HTTP.
   *
   * @command va-gov:content-release:advance-state
   * @aliases va-gov-content-release-advance-state
   * @param $state Required. Declare which state to advance to.
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
   * Get the frontend version that was requested by the user.
   *
   * @command va-gov:content-release:get-frontend-version
   * @aliases va-gov-content-release-get-frontend-version
   */
  public function getFrontendVersion() {
    $state = $this->state->get(BuildRequester::VA_GOV_FRONTEND_VERSION, '__default');
    $this->io()->write($state);
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

}
