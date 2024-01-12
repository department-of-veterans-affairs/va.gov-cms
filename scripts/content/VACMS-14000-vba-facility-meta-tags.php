<?php

/**
 * @file
 * Set VBA facility meta tags.
 *
 * VBA Facility needs to have meta tags populated
 * with data from an entity fetch field.
 *
 * VACMS-14000-vba-facility-meta-tags
 */

require_once __DIR__ . '/script-library.php';

use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(va_gov_set_vba_facility_meta_tags($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Setting the VBA facility meta tags.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_set_vba_facility_meta_tags(array &$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the count for VBA facilities.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $query->condition('type', 'vba_facility');
    $query->accessCheck(FALSE);
    $nids_to_update = $query->execute();
    $result_count = count($nids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    $sandbox['updated'] = 0;

    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['nids_to_update'] = array_combine(
      array_map('_va_gov_stringifynid', array_values($nids_to_update)),
      array_values($nids_to_update)
    );
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No VBA facility nodes found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);
  foreach ($nodes as $node) {
    /** @var \Drupal\node\NodeInterface $node */
    $nid = $node->id();
    $nvid = $node->getRevisionId();
    $node_storage = get_node_storage();
    $latest_nvid = $node_storage->getLatestRevisionId($nid);

    update_field_cc_meta_tags_table($nid, $nvid);

    // Set the latest revision, if different than the default.
    if ($nvid < $latest_nvid) {
      update_field_cc_meta_tags_table($nid, $latest_nvid);
    }

    $sandbox['updated']++;
    $nids[] = $nid;

    unset($sandbox['nids_to_update']["node_{$nid}"]);
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'VBA Facility update: Successfully updated %current nodes with meta tags from centralized content. Nodes updated: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => empty($nids) ? 'None' : implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Updating all %count VBA facility nodes with meta tags from centralized content.', [
      '%count' => $sandbox['total'],
    ]);
    return "VBA facility node updates complete. {$sandbox['current']} / {$sandbox['total']} - Total updated: {$sandbox['updated']}\n";
  }

  return "Processed nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
}

/**
 * Update the node__field_cc_meta_tags table to set the entity fetch field.
 *
 * @param string $nodeId
 *   The id of the node.
 * @param string $revisionId
 *   The id of the node revision.
 */
function update_field_cc_meta_tags_table(string $nodeId, string $revisionId) {
  $connection = \Drupal::database();
  $connection->upsert('node__field_cc_meta_tags')
    ->fields([
      'bundle' => 'vba_facility',
      'entity_id' => $nodeId,
      'revision_id' => $revisionId,
      'langcode' => 'en',
      'delta' => 0,
      'field_cc_meta_tags_value' => 0,
    ])
    ->key('revision_id')
    ->execute();
}
