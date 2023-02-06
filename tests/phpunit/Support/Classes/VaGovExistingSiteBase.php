<?php

namespace Tests\Support\Classes;

use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Support\Traits\FileLoggerTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Common functionality for ExistingSiteBase derivative test classes.
 */
abstract class VaGovExistingSiteBase extends ExistingSiteBase {

  use FileLoggerTrait;
  use ProphecyTrait;

  /**
   * Executed before each test case.
   */
  public function setUp() : void {
    parent::setUp();
    $timestamp = time();
    $date = gmdate(DATE_RFC2822);
    $testString = $this->toString();
    $message = "VA_GOV_DEBUG $timestamp $date BEFORE $testString";
    $this->writeLogMessage($message);
  }

  /**
   * Executed after each test case.
   */
  public function tearDown() : void {
    parent::tearDown();
    $timestamp = time();
    $date = gmdate(DATE_RFC2822);
    $testString = $this->toString();
    $message = "VA_GOV_DEBUG $timestamp $date AFTER $testString";
    $this->writeLogMessage($message);
  }

}
