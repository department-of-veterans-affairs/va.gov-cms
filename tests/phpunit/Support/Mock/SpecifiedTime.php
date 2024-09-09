<?php

namespace Tests\Support\Mock;

use Drupal\Component\Datetime\Time;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * Create a new instance of this class from a given timestamp.
   *
   * @param int $timestamp
   *   The current timestamp.
   *
   * @return \Tests\Support\Mock\SpecifiedTime
   *   An instance of this class configured with a specified timestamp.
   */
  public static function createFromTime(int $timestamp) {
    $t = new static(new RequestStack());
    $t->setCurrentTime($timestamp);
    return $t;
  }

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
