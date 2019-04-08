<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainArray;

/**
 * Obtainer to perform a little extra cleanup on alert titles.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAlertBlockTitles extends ObtainArray {

  /**
   * {@inheritdoc}
   */
  public static function cleanString($found) {
    $found = parent::cleanString($found);
    $found = array_map(
      function ($value) {
        // Only run if the value is not an array.
        if (!is_array($value)) {
          $parts = self::splitOnBr($value);
          return strip_tags($parts[0]);
        }
        else {
          return $value;
        }
      }, $found
    );

    return $found;
  }

}
