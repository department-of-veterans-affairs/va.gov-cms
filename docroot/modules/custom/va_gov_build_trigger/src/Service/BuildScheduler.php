<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\State\StateInterface;
use Drupal\va_gov_build_trigger\Traits\RunsDuringBusinessHours;

/**
 * The build scheduler service.
 */
class BuildScheduler implements BuildSchedulerInterface {

  use RunsDuringBusinessHours;

  public const VA_GOV_LAST_SCHEDULED_BUILD_REQUEST = 'va_gov_build_trigger.last_scheduled_build_request';

  /**
   * The build requester service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   */
  protected $buildRequester;

  /**
   * The state management service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Construct a new BuildRequester.
   *
   * @param \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface $buildRequester
   *   The build requester service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(BuildRequesterInterface $buildRequester, StateInterface $state, TimeInterface $time, DateFormatterInterface $dateFormatter) {
    $this->buildRequester = $buildRequester;
    $this->state = $state;
    $this->time = $time;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritDoc}
   */
  public function checkScheduledBuild() : void {
    $currentTime = $this->time->getCurrentTime();
    $last_scheduled_build = $this->state->get(self::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, 0);
    $time_since_last_build = ($currentTime - $last_scheduled_build);

    $this->runDuringBusinessHours(function () use ($currentTime, $time_since_last_build) {
      if ($time_since_last_build >= 3600) {
        $this->buildRequester->requestFrontendBuild('Scheduled hourly build');
        $this->state->set(self::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, $currentTime);
      }
    }, $this->buildRequester, $this->state, $time_since_last_build);
  }

}
