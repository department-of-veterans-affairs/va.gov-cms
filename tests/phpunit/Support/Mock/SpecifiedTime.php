<?php

namespace Tests\Support\Mock;

use Drupal\Component\Datetime\Time;

/**
 * A simple way to get a predictable timestamp back from the Time service.
 */
class SpecifiedTime extends Time {

  /**
   * The timestamp that getCurrentTime() should return.
   *
   * @var int
   */
  protected $timestamp;

  /**
   * Set the timestamp that getCurrentTime() should return.
   *
   * @param int $timestamp
   *   The timestamp to return.
   */
  public function setCurrentTime(int $timestamp) {
    $this->timestamp = $timestamp;
  }

  /**
   * {@inheritDoc}
   */
  public function getCurrentTime() {
    return $this->timestamp;
  }

}
