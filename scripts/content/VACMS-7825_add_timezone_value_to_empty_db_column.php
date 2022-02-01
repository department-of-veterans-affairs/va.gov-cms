<?php

/**
 * @file
 * Fields in tz columns that don't have a timezone need to have one.
 *
 * VACMS-7825_add_timezone_value_to_empty_db_column.php.
 */

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_add_tz_value_to_empty_cells($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Add tz value to empty cells in timezone column.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_add_tz_value_to_empty_cells(array &$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for VAMC System Health Service nodes.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = Drupal::database()->select('node__field_datetime_range_timezone', 'nfd');
    $query->fields('nfd', ['entity_id']);
    $query->condition('nfd.field_datetime_range_timezone_timezone', '');
    $nids_to_update = $query->execute()->fetchCol();
    $result_count = count($nids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['nids_to_update'] = array_combine(
      array_map('_va_gov_stringifynid', array_values($nids_to_update)),
      array_values($nids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No empty event timezone entries were found.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);

  foreach ($nodes as $node) {

    // Make this change a new revision.
    /** @var \Drupal\node\NodeInterface $node */
    $node->setNewRevision(TRUE);

    // Update timezone with the new value.
    $node->field_datetime_range_timezone->timezone = 'America/New_York';

    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $node->setRevisionLogMessage('Saved to set empty timezone value to America/New_York');
    $node->save();

    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Events: %current nodes timezone updated. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count Event nodes completed by va_gov_add_tz_value_to_empty_cells', [
      '%count' => $sandbox['total'],
    ]);
    return "Event node updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed Event nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
}

/**
 * Callback function to concat node ids with string.
 *
 * @param int $nid
 *   The node id.
 *
 * @return string
 *   The node id concatenated to the end of node_
 */
function _va_gov_stringifynid($nid) {
  return "node_$nid";
}
