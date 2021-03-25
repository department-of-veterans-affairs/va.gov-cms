<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Migrates Tablefield content from array of times into field_office_hours.
 *
 * @MigrateProcessPlugin(
 * id = "va_field_office_hours",
 * handle_multiples = TRUE
 * )
 *
 * Example usage:
 * @code
 * field_office_hours:
 *   plugin: va_field_office_hours
 *   source: field_hours
 * @endcode
 */
class VAFieldOfficeHours extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // This is used to convert hours data from the API into a format that
    // field_office_hours can consume.
    // Incoming data looks crazy like this.
    // @codingStandardsIgnoreStart
    //  {"friday": "8:30AM-4:30PM",
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
    $daymap = [
      'monday' => '1',
      'tuesday' => '2',
      'wednesday' => '3',
      'thursday' => '4',
      'friday' => '5',
      'saturday' => '6',
      'sunday' => '0',
    ];
    foreach ($hours as $day => $hour) {
      // Strip hour before the - for starthours.
      $start_time = strstr($hour, '-', TRUE);
      // Strip hour after the - for endhours.
      $end_time = strstr($hour, '-');
      $low_day = strtolower($day);
      if (preg_match('(AM|PM)', $hour) === 0) {
        // It is not hours, treat it as a comment.
        $hours_clean[$low_day]['comment'] = $hour;
      }
      else {
        $hours_clean[$low_day]['start_time'] = clean_time($start_time);
        $hours_clean[$low_day]['end_time'] = clean_time($end_time);
      }

    }
    if (empty($hours_clean)) {
      $return = NULL;
    }
    else {
      $week = [];
      // Create the week from data.
      foreach ($daymap as $daylong => $dayshort) {
        $week[] = [
          'day' => $dayshort,
          'starthours' => $hours_clean[$daylong]['start_time'] ?? NULL,
          'endhours' => $hours_clean[$daylong]['end_time'] ?? NULL,
          'comment' => $hours_clean[$daylong]['comment'] ?? '',
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

/**
 * Converts the Starthours and Endhours into 24hour format.
 *
 * @param string $times
 *   A string that may be a time to be cleaned and converted.
 *
 * @return string
 *   A formatted date string.
 */
function clean_time($times) {
  $time = preg_replace("/[^0-9]/", "", $times);
  $period = preg_replace("/[^a-zA-Z]/", "", $times);

  // If period is PM then perform the following time conversion.
  if ($period === "PM") {
    $time_mil = substr_replace($time, ':', -2, -2);
    $time_mil_clean = date("H:i", strtotime($time_mil . " " . $period));
    $h = substr($time_mil_clean, 0, -3);
    $m = substr($time_mil_clean, -2);
  }
  else {
    $h = substr($time, 0, -2);
    $m = substr($time, -2);
  }
  $unixtime = mktime((int) $h, (int) $m);
  $cleantime = date('Hi', $unixtime);

  return $cleantime;
}
