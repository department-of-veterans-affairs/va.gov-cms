<?php

namespace Drupal\va_gov_backend\Service;

/**
 * Datadog context provider interface.
 *
 * This interface describes an object that vends something resembling a Datadog
 * context, as would be provided by \DDTrace\current_context().
 */
interface DatadogContextProviderInterface {

  /**
   * Get the current context.
   *
   * @return array
   *   An associative array representing the current context.
   */
  public function getCurrentContext() : array;

}
