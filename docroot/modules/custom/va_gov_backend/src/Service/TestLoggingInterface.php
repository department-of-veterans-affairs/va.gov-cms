<?php

namespace Drupal\va_gov_backend\Service;

/**
 * Adds multiple test log messages to verify ddog filtering.
 */
interface TestLoggingInterface {

  /**
   * Fires tests logs to test ddog filtering.
   */
  public function runTest(): void;

}
