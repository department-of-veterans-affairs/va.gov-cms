<?php

/**
 * @file
 * Save copay_inquiries hours to office_hours.
 *
 * VACMS-9361-sync-hours-fields.php.
 */

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_force_save_rsync_hours_fields($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Save copay_inquiries hours to office_hours.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_force_save_rsync_hours_fields(array &$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for nodes with timezones in hours comments.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    // Hours field is attached to service_locations in all places,
    // save for facilities.
    // Facilities db search returned 0 results for timezones in comments.
    $query = Drupal::database()->select('node', 'n');
    $query->condition('n.type', 'vamc_system_billing_insurance', '=');
    $query->fields('n', ['nid']);
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
    return "No nodes found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);
  foreach ($nodes as $node) {
    // Make this change a new revision.
    /** @var \Drupal\node\NodeInterface $node */
    $node->setNewRevision(TRUE);
    // Copy copay hours to office hours.
    $node->field_office_hours = $node->field_hours_for_copay_inquiries_;
    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $node->setRevisionLogMessage('Copied copay hours to office hours.');
    $node->setSyncing(TRUE);
    $node->save();

    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Nodes: %current nodes copay hours copied to office hours. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count nodes completed by va_gov_force_save_rsync_hours_fields', [
      '%count' => $sandbox['total'],
    ]);
    return "Node updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
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
