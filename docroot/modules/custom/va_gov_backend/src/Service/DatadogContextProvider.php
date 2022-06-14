<?php

namespace Drupal\va_gov_backend\Service;

/**
 * Provides an object-oriented way of retrieving the current context.
 */
class DatadogContextProvider implements DatadogContextProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getCurrentContext(): array {
    // @phpstan-ignore-next-line
    return \DDTrace\current_context();
  }

}
