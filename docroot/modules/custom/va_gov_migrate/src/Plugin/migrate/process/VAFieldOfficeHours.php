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
      // First, we normalize the data.
      $hour = normalize_hour_characters($hour);
      $hour = remove_leading_zeroes($hour);
      $low_day = strtolower($day);
      // Second, we parse the data.
      if (has_one_set_of_hours($hour)) {
        // Strip hour before the "to" for starthours.
        $start_time = strstr($hour, 'to', TRUE);
        // Strip hour after the "to" for endhours.
        // Possible risk that there are comments after the hours
        // that won't be captured. (None in the data on 2023-06-09, though.)
        $end_time = strstr($hour, 'to');
        $hours_clean[$low_day]['start_time'] = clean_time($start_time);
        $hours_clean[$low_day]['end_time'] = clean_time($end_time);
      }
      else {
        // We have something other than one start and one end time.
        // Make it a comment.
        $hours_clean[$low_day]['comment'] = $hour;
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
 * Normalizes the hours characters for further parsing.
 *
 * @param string $hour_range
 *   The string of opening and closing hours for a given day.
 *
 * @return string
 *   A string formatted best for viewing.
 *   Example: 8:00 a.m. to 4:00 p.m.
 */
function normalize_hour_characters($hour_range) {
  // Set the replacement array with all characters to remove or make uppercase.
  $replace = [
    "/\s?a\.?m\.?/i" => " a.m.",
    "/\s?p\.?m\.?/i" => " p.m.",
    "/(\s?–\s?)/" => " to ",
    "/(\s?-\s?)/" => " to ",

  ];
  // Clean up the hours, based on the $replace array.
  return $hour_range = preg_replace(array_keys($replace), array_values(($replace)), $hour_range);
}

/**
 * Normalizes the hours digits for further parsing.
 *
 * @param string $hour_range
 *   The string of opening and closing hours for a given day.
 *
 * @return string
 *   A string with leading zeroes removed.
 */
function remove_leading_zeroes($hour_range) {
  // Set the replacement array with all characters to remove or make uppercase.
  $replace = [
    "/0(\d:\d\d)( [a|p]\.[m]\.)/" => "$1$2",
    "/0(\d\d\d)( [a|p]\.[m]\.)/" => "$1$2",
  ];
  // Clean up the hours, based on the $replace array.
  return $hour_range = preg_replace(array_keys($replace), array_values(($replace)), $hour_range);
}

/**
 * Checks to see if we should parse the string as hours.
 *
 * @param string $hour_range
 *   The string of opening and closing hours for a given day.
 *
 * @return bool
 *   TRUE if it has one opening and one closing time.
 */
function has_one_set_of_hours($hour_range) {
  // No hours.
  if (preg_match('(a.m.|p.m.)', $hour_range) === 0) {
    return FALSE;
  }
  // Multiple hour ranges.
  if (preg_match_all('(a.m.|p.m.)', $hour_range) > 3) {
    return FALSE;
  }
  return TRUE;
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
  $period = preg_replace("/[^(a\.m\.)|(p\.m\.)]/", "", $times);

  // If period is p.m. then perform the following time conversion.
  if ($period === "p.m.") {
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
