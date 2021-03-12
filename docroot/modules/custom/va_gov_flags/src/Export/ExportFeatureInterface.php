<?php

namespace Drupal\va_gov_flags\Export;

/**
 * Provides a service to export feature flags.
 */
interface ExportFeatureInterface {

  /**
   * Export Features.
   */
  public function export() : void;

  /**
   * Get the path of the file to export.
   *
   * @return string
   *   The path to the file to export.
   */
  public static function getPath() : string;

}
