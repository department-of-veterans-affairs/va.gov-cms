<?php

/**
 * @file
 * Re-save all VAMC system & facility health service nodes.
 *
 *  VACMS-2975-update-vamc-service-nodes-2020-10.php.
 */

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_resave_facility_service_nodes($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Re-save all VAMC system & facility health service nodes.
 *
 * @param array $sandbox
 *  Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *  Status message.
 */
function va_gov_resave_facility_service_nodes(&$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for system and facility health service nodes.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $group = $query
      ->orConditionGroup()
      ->condition('type', 'health_care_local_health_service')
      ->condition('type', 'regional_health_care_service_des');
    $nids_to_update = $query
      ->condition($group)
      ->execute();
    $result_count = count($nids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    $prefix = 'node_';
    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['nids_to_update'] = array_combine(
      array_map('_va_gov_stringifynid', array_values($nids_to_update)),
      array_values($nids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No health service nodes were found to be processed.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);

  foreach ($nodes as $node) {
    // Make this change a new revision.
    $node->setNewRevision(TRUE);

    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionAuthorId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $node->setOwnerId(1317);

    // Set revision log message.
    $node->setRevisionLogMessage('Resaved node to update title and path alias.');
    $node->save();
    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Health service nodes %current nodes saved to update the title & alias. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count health service nodes completed by va_gov_db_post_update_resave_facility_nodes.', [
      '%count' => $sandbox['total'],
    ]);
    return "Health service node re-saving complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processing health service nodes...{$sandbox['current']} / {$sandbox['total']}\n";
}

/**
 * Callback function to concat node ids with string.
 *
 * @param int $nid
 *   The node id.
 *
 * @return string
 *   The node id concatenated to the end o node_
 */
function _va_gov_stringifynid($nid) {
  return "node_$nid";
}
