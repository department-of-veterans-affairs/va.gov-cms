<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\advancedqueue\Job;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;

/**
 * The build requester service.
 */
class BuildRequester implements BuildRequesterInterface {

  public const VA_GOV_FRONTEND_VERSION = 'va_gov_build_trigger.frontend_version';
  public const VA_GOV_LAST_SCHEDULED_BUILD_REQUEST = 'va_gov_build_trigger.last_scheduled_build_request';

  /**
   * The content release queue entity.
   *
   * @var \Drupal\advancedqueue\Entity\QueueInterface
   */
  protected $buildQueue;

  /**
   * The state management service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The time service
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The date formatter service
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Construct a new BuildRequester.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity_type.manager service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface
   *   The time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface
   *   The date formatter service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, StateInterface $state, TimeInterface $time, DateFormatterInterface $dateFormatter) {
    $this->state = $state;
    $this->buildQueue = $entityTypeManager
      ->getStorage('advancedqueue_queue')
      ->load('content_release');
    $this->time = $time;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritDoc}
   */
  public function requestFrontendBuild(string $reason) : void {
    $job = Job::create('va_gov_content_release_request', ['reason' => $reason]);
    $this->buildQueue->enqueueJob($job);
  }

  /**
   * {@inheritDoc}
   */
  public function switchFrontendVersion(string $commitish) : void {
    $this->state->set(self::VA_GOV_FRONTEND_VERSION, $commitish);
  }

  /**
   * {@inheritDoc}
   */
  public function resetFrontendVersion() : void {
    $this->state->delete(self::VA_GOV_FRONTEND_VERSION);
  }

  /**
   * {@inheritDoc}
   */
  public function checkScheduledBuild() : void {
    $currentTime = $this->time->getCurrentTime();
    $day_of_week = $this->dateFormatter->format($currentTime, 'custom', 'w', 'America/New_York');
    $hour_of_day = $this->dateFormatter->format($currentTime, 'custom', 'G', 'America/New_York');
    $last_scheduled_build = $this->state->get(self::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, 0);
    $time_since_last_build = ($currentTime - $last_scheduled_build);

    $is_business_day = (1 <= $day_of_week && $day_of_week <= 5);
    $is_business_hour = (9 <= $hour_of_day && $hour_of_day <= 17);

    if ($is_business_day && $is_business_hour && ($time_since_last_build >= 3600)) {
      $this->requestFrontendBuild('Scheduled hourly build');
    }

    $this->state->set(self::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, $currentTime);
  }

}
