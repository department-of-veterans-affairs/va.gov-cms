<?php

namespace Tests\Support\Classes;

use Drupal\Tests\UnitTestCase;
use Tests\Support\Traits\FileLoggerTrait;

/**
 * Common functionality for UnitTestCase derivative test classes.
 */
abstract class VaGovUnitTestBase extends UnitTestCase {

  use FileLoggerTrait;

  /**
   * Executed before each test case.
   */
  public function setUp() {
    parent::setUp();
    $timestamp = time();
    $date = date(DATE_RFC2822);
    $testString = $this->toString();
    $message = "VA_GOV_DEBUG $timestamp $date BEFORE $testString";
    $this->writeLogMessage($message);
  }

  /**
   * Executed after each test case.
   */
  public function tearDown() {
    parent::tearDown();
    $timestamp = time();
    $date = date(DATE_RFC2822);
    $testString = $this->toString();
    $message = "VA_GOV_DEBUG $timestamp $date AFTER $testString";
    $this->writeLogMessage($message);
  }

}
