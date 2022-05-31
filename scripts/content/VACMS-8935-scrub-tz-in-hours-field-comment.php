<?php

/**
 * @file
 * Remove timezones in hours field comments.
 *
 * VACMS-8935-scrub-tz-in-hours-field-comment.php.
 */

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_force_save_remove_timezones_in_comments($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Remove timezones from hours field comments.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_force_save_remove_timezones_in_comments(array &$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for nodes with timezones in hours comments.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    // Hours field is attached to service_locations in all places,
    // save for facilities.
    // Facilities db search returned 0 results for timezones in comments.
    $paragraph_query = Drupal::database()->select('paragraph__field_office_hours', 'pfoh');
    $paragraph_query->join('node__field_service_location', 'nfsl', 'pfoh.entity_id = nfsl.field_service_location_target_id');
    $paragraph_query->fields('nfsl', ['entity_id']);
    $paragraph_query->groupBy('entity_id');
    $paragraph_query->condition('pfoh.field_office_hours_comment', '^[ecmp]s?t$|[^a-z0-9][ecmp]s?t[^a-z0-9]/i', 'REGEXP');
    $nids_to_update = $paragraph_query->execute()->fetchCol();
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

    // Wipe out comments that have timezones.
    foreach ($node->field_service_location->entity->field_office_hours as $key => $comment_field) {
      $comment_field->comment = preg_replace("/^[ecmp]s?t$|[^a-z0-9][ecmp]s?t[^a-z0-9]/i", "", $comment_field->comment);
    }

    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $node->setRevisionLogMessage('Saved to remove timezone from hours comments.');
    $node->setSyncing(TRUE);
    $node->save();

    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Nodes: %current nodes hours comments updated. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count nodes completed by va_gov_force_save_remove_timezones_in_comments', [
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
