<?php

/**
 * @file
 * Test harness for archiveIntranetOnlyForms().
 *
 * Usage:
 *   drush scr scripts/content/VACMS-20671-test-archive-intranet-only-forms.php -- [rowid]
 */

$target_rowid = $extra[0] ?? ($argv[1] ?? NULL);
$csv_path = DRUPAL_ROOT . '/sites/default/files/migrate_source/va_forms_data.csv';

if (!is_file($csv_path)) {
  throw new RuntimeException("Forms CSV not found at {$csv_path}");
}

$backup_path = sprintf('%s.bak.%s', $csv_path, date('YmdHis'));
if (!copy($csv_path, $backup_path)) {
  throw new RuntimeException("Unable to create backup at {$backup_path}");
}

$input = fopen($csv_path, 'r');
if ($input === FALSE) {
  throw new RuntimeException("Unable to open {$csv_path} for reading");
}

$temp_path = sprintf('%s.tmp.%s', $csv_path, uniqid('', TRUE));
$output = fopen($temp_path, 'w');
if ($output === FALSE) {
  fclose($input);
  throw new RuntimeException("Unable to open {$temp_path} for writing");
}

$header = fgetcsv($input);
if ($header === FALSE) {
  fclose($input);
  fclose($output);
  @unlink($temp_path);
  throw new RuntimeException('Forms CSV is empty');
}

$rowid_index = array_search('rowid', $header, TRUE);
$intranet_only_index = array_search('IntranetOnly', $header, TRUE);

if ($rowid_index === FALSE || $intranet_only_index === FALSE) {
  fclose($input);
  fclose($output);
  @unlink($temp_path);
  throw new RuntimeException('Forms CSV is missing rowid or IntranetOnly columns');
}

fputcsv($output, $header);

$updated_rowid = NULL;
while (($row = fgetcsv($input)) !== FALSE) {
  $current_rowid = $row[$rowid_index] ?? NULL;
  $should_update = FALSE;

  if ($updated_rowid === NULL && isset($row[$intranet_only_index]) && $row[$intranet_only_index] !== '1') {
    if ($target_rowid === NULL) {
      $should_update = TRUE;
    }
    elseif ((string) $current_rowid === (string) $target_rowid) {
      $should_update = TRUE;
    }
  }

  if ($should_update) {
    $row[$intranet_only_index] = '1';
    $updated_rowid = $current_rowid ?? '[missing rowid]';
  }

  fputcsv($output, $row);
}

// If target rowid was specified but not found, add it as a test row.
if ($updated_rowid === NULL && $target_rowid !== NULL) {
  $new_row = array_fill(0, count($header), '');
  $new_row[$rowid_index] = $target_rowid;
  $new_row[$intranet_only_index] = '1';
  $new_row[0] = $target_rowid; // FormNum
  $new_row[1] = "Test Form {$target_rowid}"; // FormTitle
  fputcsv($output, $new_row);
  $updated_rowid = $target_rowid;
}

fclose($input);
fclose($output);

if ($updated_rowid === NULL) {
  @unlink($temp_path);
  throw new RuntimeException('No non-IntranetOnly rows were found to update');
}

if (!rename($temp_path, $csv_path)) {
  @unlink($temp_path);
  throw new RuntimeException("Unable to replace {$csv_path} with updated CSV");
}

print "Updated rowid: {$updated_rowid}\n";
print "Backup created at: {$backup_path}\n";

\Drupal::service('va_gov_migrate.va_gov_migrate_service')->archiveIntranetOnlyForms();

print "archiveIntranetOnlyForms() completed.\n";