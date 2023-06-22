<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Converts a boolean value to a Drupal boolean value.
 * Example: TRUE => 1, FALSE => 0.
 *
 * @MigrateProcessPlugin(
 *   id = "convert_boolean"
 * )
 *
 * Example usage:
 * @code
 * field_mobile:
 *   plugin: convert_boolean
 *   source: mobile
 * @endcode
 */
class ConvertBoolean extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_string($value) && $value === 'yes' || $value === 'no') {
      if ($value === 'yes') {
        $value = TRUE;
      }
      else {
        $value = FALSE;
      }
    }
    return boolval($value);
  }

}
