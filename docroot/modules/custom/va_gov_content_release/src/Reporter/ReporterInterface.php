<?php

namespace Drupal\va_gov_content_release\Reporter;

/**
 * An interface for the content release reporter service.
 *
 * This service is used to report events in the content release process.
 *
 * These messages are posted directly as received, not handling string
 * translation, etc, because of how many services are embedded in calls
 * to StringTranslationTrait::t() :(.
 */
interface ReporterInterface {

  /**
   * Report an "info" message.
   *
   * @param string $message
   *   The message.
   */
  public function reportInfo(string $message) : void;

  /**
   * Report an error.
   *
   * @param string $message
   *   The error message.
   * @param \Throwable $exception
   *   The exception.
   */
  public function reportError(string $message, \Throwable $exception = NULL) : void;

}
