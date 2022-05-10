<?php

/**
 * @file
 * Force save 'limited html' on all system service descriptions.
 *
 * VACMS-7662-node-regional_health_care_service_des-force-limited-rich-text.php.
 */

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_force_save_limited_html_on_service_descriptions($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Set field_body format to rich_text_limited.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_force_save_limited_html_on_service_descriptions(array &$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for VAMC System Health Service nodes.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = Drupal::database()->select('node_field_data', 'nfd');
    $query->join('node__field_body', 'nfb', 'nfd.nid = nfb.entity_id');
    $query->fields('nfd', ['nid']);
    $query->condition('nfd.type', 'regional_health_care_service_des');
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
    return "No vamc system health service nodes were found to be processed.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);

  foreach ($nodes as $node) {

    // Make this change a new revision.
    /** @var \Drupal\node\NodeInterface $node */
    $node->setNewRevision(TRUE);
    $filtered = check_markup($node->get('field_body')->value, 'rich_text_limited', '');

    // Update field_body with the new value.
    $node->field_body->setValue(
        [
          'value' => $filtered,
          'format' => 'rich_text_limited',
        ]
      );

    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $node->setRevisionLogMessage('Saved to set body description to limited rich text.');
    $node->setSyncing(TRUE);
    $node->save();

    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'VAMC System Health Service: %current nodes body format updated. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count VAMC System Health Service nodes completed by va_gov_force_save_limited_html_on_service_descriptions', [
      '%count' => $sandbox['total'],
    ]);
    return "VAMC System Health Service node updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed VAMC System Health Service nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
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
