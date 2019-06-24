<?php

namespace Drupal\va_gov_flags\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Implementing our JSON api.
 */
class VaGovFlagsController {

  /**
   * Callback for the API.
   */
  public function renderApi() {

    return new JsonResponse([
      'data' => $this->getResults(),
      'method' => 'GET',
    ]);
  }

  /**
   * A helper function returning results.
   */
  public function getResults() {
    // Grab our feature flag names.
    $flag_status = \Drupal::service('feature_toggle.feature_status');
    $dump = $flag_status->getStatus('flag2');
    $flag_config = \Drupal::config('feature_toggle.features');
    $flag_names = $flag_config->get();
    $flag_toggle = [];
    // Recurse through the names to get their status.
    foreach ($flag_names['features'] as $key => $flag) {
      $flag_toggle[$flag] = !empty($flag_status->getStatus($key))
      ? $flag_status->getStatus($key)
      : FALSE;
    }
    return $flag_toggle;
  }

}
