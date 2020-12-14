<?php

namespace Drupal\va_gov_flags;

/**
 * A class to query feature flags.
 */
interface FeatureFlagDataBuilderInterface {

  /**
   * Get an array of Feature Flag and their status.
   *
   * @return array
   *   An array of feature ['name' => 'label'].
   */
  public function getFeatures() : array;

  /**
   * Get the data in a specified format.
   *
   * @return array
   *   An array of the data in the specified format.
   */
  public function buildData() : array;

}
