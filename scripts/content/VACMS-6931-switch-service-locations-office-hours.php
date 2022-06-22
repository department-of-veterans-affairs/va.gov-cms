<?php

/**
 * @file
 * Service Location: move table field data to office hours field.
 *
 * VACMS-6931-switch-service-locations-office-hours.php.
 */

use Psr\Log\LogLevel;

// Begin paragraph processing.
$sandbox = ['#finished' => 0];
// Pre-populating data header.
$audit_data[] = [
  'Paragraph ID',
  'Original Value',
  'Start Time',
  'End Time',
  'Comment',
];
do {
  print(va_gov_switch_service_locations_office_hours($sandbox, $audit_data));
} while ($sandbox['#finished'] < 1);

// Paragraph processing complete - write to audit file.
$file_name = 'office_hours_outliers.csv';
// Open csv file for auditing outliers.
$audit_file = fopen($file_name, 'w');
if ($audit_file === FALSE) {
  die('Error opening the file ' . $file_name);
}

foreach ($audit_data as $audit_row) {
  fputcsv($audit_file, $audit_row);
}

fclose($audit_file);

return;

/**
 * Migrate hours data from table field to office hours field.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param array $audit_data
 *   Array for storing data outliers so we can report on them.
 *
 * @return string
 *   Status message.
 */
function va_gov_switch_service_locations_office_hours(array &$sandbox, array &$audit_data) {
  $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');

  // Get the Service Location paragraph count. This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $paragraph_storage->getQuery();
    $pids_to_update = $query->condition('type', 'service_location')->execute();
    $result_count = count($pids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;

    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['pids_to_update'] = array_combine(
      array_map('_va_gov_stringifypid', array_values($pids_to_update)),
      array_values($pids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No service location paragraphs were found to be processed.\n";
  }

  $limit = 25;

  // Load entities.
  $paragraph_ids = array_slice($sandbox['pids_to_update'], 0, $limit, TRUE);
  $service_locations = $paragraph_storage->loadMultiple($paragraph_ids);

  foreach ($service_locations as $service_location) {
    // Grab the value for the old hours table field.
    $value = reset($service_location->get('field_facility_service_hours')->getValue())['value'];
    // Make sure we are dealing with an array, fail silently otherwise.
    $days = (!empty($value) && is_array($value)) ? $value : [];

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

    $weekdaymap = [
      'Mon' => 'monday',
      'Tue' => 'tuesday',
      'Wed' => 'wednesday',
      'Thu' => 'thursday',
      'Fri' => 'friday',
      'Sat' => 'saturday',
      'Sun' => 'sunday',
    ];

    foreach ($days as $day) {
      $comment = '';

      // Three character abbreviation for day of week (Mon - Sun).
      $dayofweek = trim($day[0]);
      $low_day = $weekdaymap[$dayofweek];
      $raw_value = trim($day[1]);

      // Store audit data for this day.
      $hours_clean[$low_day]['id'] = $service_location->id();
      $hours_clean[$low_day]['raw'] = $raw_value;

      // Normalize meridiem designations.
      $new_value = _normalize_meridiem($raw_value);

      // Normalize time separator designations.
      $new_value = _normalize_separator($new_value);

      // Gather some initial data about our new string.
      preg_match_all('/(a\.m\.|p\.m\.)/', $new_value, $meridiems);
      $meridiem_count = count($meridiems[0]);
      preg_match_all('/(ZZZ)/', $new_value, $separators);
      $separator_count = count($separators[0]);

      // If the string doesn't start with a number, treat as a comment.
      if (!ctype_digit(substr(trim($raw_value), 0, 1))) {
        $hours_clean[$low_day]['comment'] = _clean_comment($raw_value);
      }
      elseif ($meridiem_count != 2) {
        // If the number of meridiems is not two, treat as a comment.
        $hours_clean[$low_day]['comment'] = _clean_comment($raw_value);
      }
      elseif (!$separator_count || $separator_count > 2) {
        // If the separator count is neither 1 nor 2, treat as a comment.
        $hours_clean[$low_day]['comment'] = _clean_comment($raw_value);
      }
      else {
        // Break up the normalized string by the separator.
        $pieces = explode('ZZZ', $new_value);

        // The first two pieces must contain meridiems to be treated as a time.
        if ((preg_match('/(a\.m\.|p\.m\.)/', $pieces[0]) === 0)
        || (preg_match('/(a\.m\.|p\.m\.)/', $pieces[1]) === 0)) {
          // If no meridiems in first two pieces, treat as a comment.
          $hours_clean[$low_day]['comment'] = _clean_comment($raw_value);
        }
        else {
          // Process the first piece as a time value.
          $start_time = _clean_time($pieces[0]);
          $sub_pieces = [];

          // The second piece could still contain a comment.
          if (str_contains($pieces[1], 'a.m.')) {
            $sub_pieces = explode('a.m.', $pieces[1]);
            $end_time = _clean_time($sub_pieces[0] . 'a.m.');
          }
          elseif (str_contains($pieces[1], 'p.m.')) {
            $sub_pieces = explode('p.m.', $pieces[1]);
            $end_time = _clean_time($sub_pieces[0] . 'p.m.');
          }

          // If the start time is greater than the end time, treat as a comment.
          if ($start_time > $end_time) {
            $hours_clean[$low_day]['comment'] = _clean_comment($raw_value);
          }
          else {
            // Set the time values.
            $hours_clean[$low_day]['start_time'] = $start_time;
            $hours_clean[$low_day]['end_time'] = $end_time;

            // Add any remaining sub_pieces to comment.
            array_shift($sub_pieces);
            $comment = implode(' ', $sub_pieces);

            // If a third piece remains treat as a comment.
            if (count($pieces) == 3) {
              $comment .= $pieces[2];
            }

            // A final clean for the comment to remove noise.
            $hours_clean[$low_day]['comment'] = _clean_comment($comment);
          }
        }
      }
    }

    if (!empty($hours_clean)) {
      $week = [];
      $common_comments = [
        'ET',
        'CT',
        'MT',
        'PT',
        'Closed',
        '24/7',
      ];
      // Create the week from data.
      foreach ($daymap as $daylong => $dayshort) {
        $week[] = [
          'day' => $dayshort,
          'starthours' => $hours_clean[$daylong]['start_time'] ?? NULL,
          'endhours' => $hours_clean[$daylong]['end_time'] ?? NULL,
          'comment' => $hours_clean[$daylong]['comment'] ?? '',
        ];

        // Log outliers to CSV.
        if (!empty($hours_clean[$daylong]['comment'])
        && !in_array($hours_clean[$daylong]['comment'], $common_comments)
        && ($hours_clean[$daylong]['comment'] != $hours_clean[$daylong]['raw'] || (empty($hours_clean[$daylong]['start_time']) && empty($hours_clean[$daylong]['end_time'])))) {
          $audit_data[] = [
            $hours_clean[$daylong]['id'],
            $hours_clean[$daylong]['raw'],
            $hours_clean[$daylong]['start_time'] ?? '',
            $hours_clean[$daylong]['end_time'] ?? '',
            $hours_clean[$daylong]['comment'] ?? '',
          ];
        }
      }

      // Use converted data from $week as new field_office_hours value.
      $service_location->set('field_office_hours', $week);
      $service_location->save();
    }

    unset($sandbox['pids_to_update'][_va_gov_stringifypid($service_location->id())]);
    $pids[] = $service_location->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['pids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Service Location: %current paragraphs office hours converted. Paragraphs processed: %pids', [
      '%current' => $sandbox['current'],
      '%pids' => implode(', ', $pids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count Service Location paragraphs completed by va_gov_switch_service_locations_office_hours.', [
      '%count' => $sandbox['total'],
    ]);
    return "Service Location paragraphs updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed Service Location paragraphs... {$sandbox['current']} / {$sandbox['total']}.\n";

}

/**
 * Replaces all variants of AM and PM with a standard format.
 *
 * @param string $value
 *   A string that may contain a start and end time.
 *
 * @return string
 *   A formatted string where AM and PM have been standardized.
 */
function _normalize_meridiem($value) {
  // Normalize ante meridiem.
  $new_value = preg_replace("/(am|a\.m |a\.m$)/i", "a.m.", $value);

  // Normalize post meridiem.
  $new_value = preg_replace("/(pm|p\.m |p\.m$)/i", "p.m.", $new_value);

  // Replace noon variants with 12:00 p.m.
  $new_value = preg_replace("/(noon|12 noon|12:00 noon)/i", "12:00 p.m.", $new_value);

  // Replace midnight variants with 12:00 a.m.
  $new_value = preg_replace("/(midnight|12 midnight|12:00 midnight)/i", "12:00 a.m.", $new_value);

  return $new_value;
}

/**
 * Attempts to standardize the characters used to define a time range.
 *
 * @param string $value
 *   A string that may contain a start time, separator, and end time.
 *
 * @return string
 *   A formatted string where the separator has been standardized.
 */
function _normalize_separator($value) {
  // Normalize the time separator.
  $new_value = preg_replace("/(to|-|–)/i", "ZZZ", $value);

  return $new_value;
}

/**
 * Remove whitespace and other unwanted charactes from the ends of a string.
 *
 * @param string $value
 *   A string that may include unwanted leading and trailing characters.
 *
 * @return string
 *   A formatted string where the unwanted characters have been removed.
 */
function _clean_comment($value) {
  // Remove certain "garbage" characters from the string.
  $new_value = trim($value, "/");
  $new_value = trim($new_value, "*");
  $new_value = trim($new_value, ",");
  $new_value = trim($new_value);

  // Standardize time zones.
  $new_value = preg_replace("/^(est|est\.|et\.)$/i", "ET", $new_value);
  $new_value = preg_replace("/^(cst|cst\.|ct\.)$/i", "CT", $new_value);
  $new_value = preg_replace("/^(mst|mst\.|mt\.)$/i", "MT", $new_value);
  $new_value = preg_replace("/^(pst|pst\.|pt\.)$/i", "PT", $new_value);

  // If the comment is a variant of the word closed, standardize.
  $new_value = preg_replace("/^(closed)$/i", "Closed", $new_value);
  $new_value = preg_replace("/^(Not available)$/i", "Closed", $new_value);
  $new_value = preg_replace("/^(NA)$/i", "Closed", $new_value);

  return $new_value;
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
function _clean_time($times) {
  $time = preg_replace("/[^0-9]/", "", $times);
  if (strlen($time) < 3) {
    // An incomplete time value was supplied (hour without minutes).
    // Example: 6 a.m. instead of 6:00 a.m.
    // Add the missing minutes.
    $time = $time . "00";
  }

  // If period contains "pm" then perform the following time conversion.
  if (str_contains($times, 'p.m')) {
    $time_mil = substr_replace($time, ':', -2, -2);
    $time_mil_clean = date("H:i", strtotime($time_mil . " pm"));
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

/**
 * Callback function to concat paragraph ids with string.
 *
 * @param int $pid
 *   The paragraph id.
 *
 * @return string
 *   The paragraph id appended to the end of paragraph_.
 */
function _va_gov_stringifypid($pid) {
  return "paragraph_$pid";
}
