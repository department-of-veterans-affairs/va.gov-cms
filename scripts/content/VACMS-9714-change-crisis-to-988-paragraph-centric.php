<?php

/**
 * @file
 * Service Location: move table field data to office hours field.
 *
 * VACMS-6931-switch-service-locations-office-hours.php.
 */

use Psr\Log\LogLevel;
$ops_status_total_pattern = "(\<a.*\>)?(1[\-\.])?800[\-\.]273[\-\.]8255(\<\/a\>)?";
$ops_status_pattern = "/(\<a.*\>)?(1[\-\.])?800[\-\.]273[\-\.]8255(\<\/a\>)?/i";
$replacement_string = '<a aria-label="9 8 8" href="tel:988">988</a>';


// Begin paragraph processing.
$sandbox = ['#finished' => 0];
do {
  print(va_gov_switch_service_locations_office_hours($sandbox, $ops_status_total_pattern, $ops_status_pattern, $replacement_string));
} while ($sandbox['#finished'] < 1);

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
function va_gov_switch_service_locations_office_hours(array &$sandbox, $pattern_string, $pattern_regex, $replacement_string) {
  $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');

  // Get the Service Location paragraph count. This runs only once.
  if (!isset($sandbox['total'])) {
    // $query = $paragraph_storage->getQuery();
    $query = Drupal::database()->select('paragraph__field_wysiwyg', 'wysiwyg');
    $query->join('node__field_content_block', 'nfcb', 'wysiwyg.entity_id = nfcb.field_content_block_target_id');
    $query->fields('nfcb', ['entity_id','field_content_block_target_id']);
    $query->groupBy('entity_id');
    $query->condition('wysiwyg.field_wysiwyg_value', $pattern_string, 'REGEXP');
    $pids_to_update = $query->execute()->fetchAllKeyed();
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
    $value = reset($service_location->get('field_wysiwyg')->getValue())['value'];
    // Make sure we are dealing with an array, fail silently otherwise.
    $paras = (!empty($value)) ? $value : "";
    $old_value = $paras;
    $new_value = preg_replace($pattern_regex, $replacement_string, $old_value);
    $service_location->set( 'field_wysiwyg', [
          'value' => $new_value,
          'format' => 'rich_text',
        ]
        );
        // $service_location->setRevisionAuthorId(1317);
        // $service_location->setChangedTime(time());
        // $service_location->setRevisionCreationTime(time());
        // $service_location->setRevisionLogMessage('Updated Crisis number from 800-237-8255 to 988');
        // $service_location->setSyncing(TRUE);
    $service_location->save();

    unset($sandbox['pids_to_update'][_va_gov_stringifypid($service_location->id())]);
    $pids[] = $service_location->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['pids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'VAMC Detail Page Wysiwyg: %current paragraphs phone numbers updated. Paragraphs processed: %pids', [
      '%current' => $sandbox['current'],
      '%pids' => implode(', ', $pids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count VAMC Detail Page Wysiwyg va_gov_switch_service_locations_office_hours-cb-rev.', [
      '%count' => $sandbox['total'],
    ]);
    return "Service Location paragraphs updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed Service Location paragraphs... {$sandbox['current']} / {$sandbox['total']}.\n";

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
