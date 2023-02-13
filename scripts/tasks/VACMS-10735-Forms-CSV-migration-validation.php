<?php

/**
 * @file
 * CSV Validation for VA Forms CSV.
 */

run();

/**
 * The run function that calls everything else.
 */
function run() {
  $exitMessageBaseText = 'Find-a-form: CSV validation failed;';
  $csv = _va_forms_get_csv_headers();
  _va_forms_validate_csv_has_content($csv, $exitMessageBaseText);
  _va_forms_validate_csv_headers($csv, $exitMessageBaseText);
  // @todo add a date check on the file to see if it is not more than 3 days
  // old (to allow for weekends).
}

/**
 * Gets the csv headers from the known forms data csv.
 *
 * @return array
 *   An array of headers from the csv
 */
function _va_forms_get_csv_headers() {
  /** @var \Drupal\Core\StreamWrapper\StreamWrapperManager $wrapper */
  $wrapper = \Drupal::service('stream_wrapper_manager');
  $basePath = $wrapper->getViaScheme('public')->realpath();
  $filePath = "{$basePath}/migrate_source/va_forms_data.csv";
  $handle = fopen($filePath, 'r');
  return fgetcsv($handle, NULL);
}

/**
 * Validates the VA Form CSV contains the expected headers.
 *
 * @param array $headers
 *   The VA forms headers.
 * @param string $exitMessage
 *   The exit message base text.
 */
function _va_forms_validate_csv_headers(array $headers, string $exitMessage) {
  // Validate the headers against the VA forms migration config fields.
  $config = \Drupal::config('migrate_plus.migration.va_node_form');
  $data = $config->getRawData();
  $fields = $data['source']['fields'];
  $fieldNames = array_column($fields, 'name');
  $diff = array_diff($headers, $fieldNames);
  if (!empty($diff)) {
    exit("{$exitMessage} Failed matching headers in _va_forms_validate_csv_headers()\r\n");
  }
}

/**
 * Validates the csv file has content (is not zero bytes).
 *
 * When fgetcsv() returns a value, it is either an associative array, or FALSE.
 * Checking for FALSE allows us to quickly determine if the CSV was parsed
 * correctly.
 *
 * @param bool|array $csv
 *   The current csv row as an array, or false if not a valid csv.
 * @param string $exitMessage
 *   The exit message base text.
 */
function _va_forms_validate_csv_has_content(bool|array $csv, string $exitMessage) {
  if ($csv === FALSE) {
    exit("{$exitMessage} Empty or invalid CSV. In _va_forms_validate_csv_contents()\r\n");
  }
}
