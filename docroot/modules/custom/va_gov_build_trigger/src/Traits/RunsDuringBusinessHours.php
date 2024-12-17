<?php

namespace Drupal\va_gov_build_trigger\Traits;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Used for classes that need to run things only during business hours.
 */
trait RunsDuringBusinessHours {

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
   * Set the time service.
   */
  protected function setTimeService(TimeInterface $time) {
    $this->time = $time;
  }

  /**
   * Get the time service.
   *
   * @return \Drupal\Component\Datetime\TimeInterface
   *   The time service.
   */
  protected function getTimeService() : TimeInterface {
    if (!isset($this->time) || !($this->time instanceof TimeInterface)) {
      throw new \Exception('time property must be defined in ' . __CLASS__);
    }

    return $this->time;
  }

  /**
   * Set the date formatter service.
   */
  protected function setDateFormatterService(DateFormatterInterface $dateFormatter) {
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * Get the date formatter service.
   *
   * @return \Drupal\Core\Datetime\DateFormatterInterface
   *   The date formatter service.
   */
  protected function getDateFormatterService() : DateFormatterInterface {
    if (!isset($this->dateFormatter) || !($this->dateFormatter instanceof DateFormatterInterface)) {
      throw new \Exception('dateFormatter property must be defined in ' . __CLASS__);
    }

    return $this->dateFormatter;
  }

  /**
   * Determine if the current time is during business hours.
   *
   * @return bool
   *   Whether or not the current time is during business hours.
   */
  protected function isCurrentlyDuringBusinessHours() : bool {
    $currentTime = $this->getTimeService()->getCurrentTime();
    $day_of_week = $this->getDateFormatterService()->format($currentTime, 'custom', 'w', 'America/New_York', LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $hour_of_day = $this->getDateFormatterService()->format($currentTime, 'custom', 'G', 'America/New_York', LanguageInterface::LANGCODE_NOT_APPLICABLE);

    $is_business_day = (1 <= $day_of_week && $day_of_week <= 5);
    $is_business_hour = (9 <= $hour_of_day && $hour_of_day < 20);

    return ($is_business_day && $is_business_hour);
  }

  /**
   * Call a function only during business hours.
   *
   * @param callable $f
   *   The function (or other callable) to call.
   * @param mixed $args
   *   Any arguments to pass.
   *
   * @return mixed
   *   Whatever $f returns.
   */
  protected function runDuringBusinessHours(callable $f, ...$args) {
    if ($this->isCurrentlyDuringBusinessHours()) {
      return call_user_func($f, $args);
    }
  }

}
