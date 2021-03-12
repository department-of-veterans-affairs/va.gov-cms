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

      // Strip hour before the - for starthours.
      $starthours = strstr($hour, '-', TRUE);
      // Strip hour after the - for endhours.
      $endhours = strstr($hour, '-');

      // Send starthours for time cleanup.
      $clean_start_time = clean_time($starthours);
      // Send endhours for time cleanup.
      $clean_end_time = clean_time($endhours);

      // Convert time to 24 hour format and store for future use.
      $arr_starthours[] = date("Hi", strtotime($clean_start_time));
      $arr_endhours[] = date("Hi", strtotime($clean_end_time));

      // Store clean starthours/endhours/non-hours into array for future use.
      switch (strtolower($day)) {
        case "sunday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['sunday']['comment'] = $hour;
          }
          else {
            $hours_clean['sunday']['starthours'] = $arr_starthours[2];
            $hours_clean['sunday']['endhours'] = $arr_endhours[2];
          }
          break;

        case "monday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['monday']['comment'] = $hour;
          }
          else {
            $hours_clean['monday']['starthours'] = $arr_starthours[1];
            $hours_clean['monday']['endhours'] = $arr_endhours[1];
          }
          break;

        case "tuesday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['tuesday']['comment'] = $hour;
          }
          else {
            $hours_clean['tuesday']['starthours'] = $arr_starthours[3];
            $hours_clean['tuesday']['endhours'] = $arr_endhours[3];
          }
          break;

        case "wednesday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['wednesday']['comment'] = $hour;
          }
          else {
            $hours_clean['wednesday']['starthours'] = $arr_starthours[6];
            $hours_clean['wednesday']['endhours'] = $arr_endhours[6];
          }
          break;

        case "thursday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['thursday']['comment'] = $hour;
          }
          else {
            $hours_clean['thursday']['starthours'] = $arr_starthours[5];
            $hours_clean['thursday']['endhours'] = $arr_endhours[5];
          }
          break;

        case "friday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['friday']['comment'] = $hour;
          }
          else {
            $hours_clean['friday']['starthours'] = $arr_starthours[0];
            $hours_clean['friday']['endhours'] = $arr_endhours[0];
          }
          break;

        case "saturday":
          if (preg_match('(AM|PM)', $hour) === 0) {
            $hours_clean['saturday']['comment'] = $hour;
          }
          else {
            $hours_clean['saturday']['starthours'] = $arr_starthours[4];
            $hours_clean['saturday']['endhours'] = $arr_endhours[4];
          }
          break;

      }

    }
    if (empty($hours_clean)) {
      $return = NULL;
    }
    else {
      $daymap = [
        'monday' => '1',
        'tuesday' => '2',
        'wednesday' => '3',
        'thursday' => '4',
        'friday' => '5',
        'saturday' => '6',
        'sunday' => '0',
      ];

      $week = [];
      // Create the week from data.
      foreach ($daymap as $daylong => $dayshort) {
        $week[] = [
          'day' => $dayshort,
          'starthours' => $hours_clean[$daylong]['starthours'],
          'endhours' => $hours_clean[$daylong]['endhours'],
          'comment' => $hours_clean[$daylong]['comment'],
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

    $unixtime = mktime($h, $m);
    $unixtime_convert = date("H:i A", $unixtime);
    $cleantime = preg_replace("/[^0-9]/", '', $unixtime_convert);

  }
  else {
    $h = substr($time, 0, -2);
    $m = substr($time, -2);

    $unixtime = mktime((int) $h, (int) $m);
    $cleantime = date("H:i A", $unixtime);
  }

  return $cleantime;
}
