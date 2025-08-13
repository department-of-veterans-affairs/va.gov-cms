<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Service\BuildSchedulerInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;

/**
 * The release checker service.
 */
class ReleaseChecker {

  use LoggerChannelTrait;
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
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

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
   */
  public function __construct(
    ReleaseStateManagerInterface $releaseStateManager,
    BuildSchedulerInterface $buildScheduler,
    RequestInterface $requestService,
    StateInterface $state,
  ) {
    $this->releaseStateManager = $releaseStateManager;
    $this->buildScheduler = $buildScheduler;
    $this->requestService = $requestService;
    $this->state = $state;
    $this->logger = $this->getLogger('va_gov_build_trigger');
  }

  /**
   * Reset the content release state.
   */
  public function resetState() {
    $this->releaseStateManager->resetState();
    $this->logger->info('Content release state has been reset to \'ready\'.');
  }

  /**
   * If the state is stale, reset the state.
   */
  public function checkStale() {
    if ($this->releaseStateManager->releaseStateIsStale()) {
      $this->resetState();
      $this->requestService->submitRequest('Submitting new request due to staleness.');
    }
  }

  /**
   * Make sure builds are going out at least hourly during business hours.
   */
  public function checkScheduledBuild() {
    $this->buildScheduler->checkScheduledBuild();
  }

}
