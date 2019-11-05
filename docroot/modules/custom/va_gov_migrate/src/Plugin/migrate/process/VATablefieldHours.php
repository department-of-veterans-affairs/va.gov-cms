<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Migrates Tablefield content from array of times into table_field hours in D8.
 *
 * @MigrateProcessPlugin(
 * id = "va_table_field_hours",
 * handle_multiples = TRUE
 * )
 *
 * Example usage:
 * @code
 * field_table:
 *   plugin: va_table_field_hours
 *   source: field_table
 * @endcode
 */
class VATablefieldHours extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // This is used to convert hours data from the API into a format that
    // field_table can consume.
    // Incoming data looks crazy like this.
    // @codingStandardsIgnoreStart
    //  {"friday": "830AM-430PM",
    //  "monday": "830AM-700PM",
    //  "sunday": "Closed",
    //  "tuesday": "830AM-700PM",
    // "saturday": "Closed",
    //  "thursday": "830AM-600PM",
    //  "wednesday": "830AM-600PM"}
    // @codingStandardsIgnoreEnd
    // Make sure we are dealing with an array, fail silently otherwise.
    $hours = (!empty($value) && is_array($value)) ? $value : [];
    $hours_clean = [];
    foreach ($hours as $day => $hour) {
      // Decapitalize the keys.  Both 'friday' and 'Friday' appear in the keys.
      $hours_clean[strtolower($day)] = $hour;
    }
    if (empty($hours_clean)) {
      $return = NULL;
    }
    else {
      $daymap = [
        'monday' => 'Mon',
        'tuesday' => 'Tue',
        'wednesday' => 'Wed',
        'thursday' => 'Thu',
        'friday' => 'Fri',
        'saturday' => 'Sat',
        'sunday' => 'Sun',
      ];

      $week = [];
      // Create the week from data.
      foreach ($daymap as $daylong => $dayshort) {
        $week[] = [
          0 => $dayshort,
          1 => (!empty($hours_clean[$daylong])) ? $hours_clean[$daylong] : '-',
        ];
      }
      $return = $week;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
