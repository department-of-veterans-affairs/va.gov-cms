<?php

/**
 * @file
 * Assign revision timestamps to terms.
 *
 * VACMS-830-taxonomy_term_revisions_fix.
 */

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_add_timestamp_to_revisions($sandbox));
} while ($sandbox['#finished'] < 1);
// Term processing complete.  Call this done.
return;

/**
 * Grab term revisions without timestamps and insert tid changed time.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_add_timestamp_to_revisions(array &$sandbox) {
  $connection = Drupal::database();
  // Get the term count.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $connection->select('taxonomy_term_field_data', 'ttf');
    $query->join('taxonomy_term_revision', 'ttr', 'ttr.tid = ttf.tid');
    // taxonomy_term_field_data doesn't have "created" column,
    // but does have "changed".
    $query->fields('ttf', ['tid', 'changed']);
    $query->condition('ttr.revision_created', 'NULL', 'IS NULL');
    $terms = $query->execute()->fetchAllKeyed();
    $result_count = count($terms);
    $sandbox['terms'] = $terms;
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    // Create non-numeric keys to accurately remove each tid when processed.
    $sandbox['tids_to_update'] = array_combine(
      array_map('_va_gov_stringifytid', array_keys($terms)),
      array_keys($terms));
  }

  // Do not continue if no tids are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No terms were found to be processed.\n";
  }

  $limit = 25;
  $terms_sliced = array_slice($sandbox['tids_to_update'], 0, $limit, TRUE);
  foreach ($terms_sliced as $tid) {
    $connection->update('taxonomy_term_revision')
      ->fields([
        'revision_created' => !empty($sandbox['terms'][$tid]) ? $sandbox['terms'][$tid] : 0,
      ])
      ->condition('tid', $tid, '=')
      ->execute();

    unset($sandbox['tids_to_update']["term_$tid"]);
    $term_ids[] = $tid;
    $sandbox['current'] = $sandbox['total'] - count($sandbox['tids_to_update']);
  }

  // Log the processed tids.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Terms: %current tids updated. Tids processed: %tids', [
      '%current' => $sandbox['current'],
      '%tids' => implode(', ', $term_ids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Updating %count terms by va_gov_add_timestamp_to_revisions', [
      '%count' => $sandbox['total'],
    ]);
    return "Term updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed terms... {$sandbox['current']} / {$sandbox['total']}.\n";
}

/**
 * Callback function to concat term ids with string.
 *
 * @param int $tid
 *   The term id.
 *
 * @return string
 *   The term id concatenated to the end of term_
 */
function _va_gov_stringifytid($tid) {
  return "term_$tid";
}
