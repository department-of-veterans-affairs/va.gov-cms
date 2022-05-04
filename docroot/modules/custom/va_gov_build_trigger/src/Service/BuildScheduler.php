<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\State\StateInterface;

/**
 * The build scheduler service.
 */
class BuildScheduler implements BuildSchedulerInterface {

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
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

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
    $day_of_week = $this->dateFormatter->format($currentTime, 'custom', 'w', 'America/New_York', LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $hour_of_day = $this->dateFormatter->format($currentTime, 'custom', 'G', 'America/New_York', LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $last_scheduled_build = $this->state->get(self::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, 0);
    $time_since_last_build = ($currentTime - $last_scheduled_build);

    $is_business_day = (1 <= $day_of_week && $day_of_week <= 5);
    $is_business_hour = (9 <= $hour_of_day && $hour_of_day < 17);

    if ($is_business_day && $is_business_hour && ($time_since_last_build >= 3600)) {
      $this->buildRequester->requestFrontendBuild('Scheduled hourly build');
      $this->state->set(self::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, $currentTime);
    }
  }

}
