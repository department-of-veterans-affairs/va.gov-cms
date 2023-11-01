<?php

/**
 * @file
 * Copy Vet Center - Outstation names to field_official_name.
 *
 * Vet Center - Outstation now supports a common name
 * (usually associated with place) and an official name.
 *
 * VACMS-15422-vet-center-outstation-copy-title.php.
 */

require_once __DIR__ . '/script-library.php';

use Psr\Log\LogLevel;

$revision_message = 'In a separate action, the current facility name was copied from the node title to the "Name of Vet Center - Outstation" field.';
$sandbox = ['#finished' => 0];
do {
  print(va_gov_copy_vet_center_os_title($sandbox, $revision_message));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Copy node title to field_official_name.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param string $revision_message
 *   Text to be used in revision log message.
 *
 * @return string
 *   Status message.
 */
function va_gov_copy_vet_center_os_title(array &$sandbox, $revision_message) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the count for Vet Center - Outstations.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $query->condition('type', 'vet_center_outstation');
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
    return "No Vet Center - Outstation nodes found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);
  foreach ($nodes as $node) {
    /** @var \Drupal\node\NodeInterface $node */
    $nid = $node->id();
    $nvid = $node->getRevisionId();
    $original_name = $node->getTitle();
    $node->field_official_name->value = $original_name;

    // Grab the latest revision before we save this one.
    $latest_revision = get_node_at_latest_revision($nid);
    save_node_revision($node, $revision_message, FALSE);

    // If a revision (draft) newer than the default exists, update it as well.
    if ($nvid !== $latest_revision->getRevisionId()) {
      $original_name = $latest_revision->getTitle();
      $latest_revision->field_official_name->value = $original_name;
      save_node_revision($latest_revision, $revision_message, FALSE);
      unset($latest_revision);
    }

    $sandbox['updated']++;
    $nids[] = $nid;

    unset($sandbox['nids_to_update']["node_{$nid}"]);
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Vet Center - Outstation update: Successfully copied %current node title fields to the "Name of Vet Center - Outstation" fields. Nodes updated: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => empty($nids) ? 'None' : implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Copying all %count Vet Center - Oustation title fields completed.', [
      '%count' => $sandbox['total'],
    ]);
    return "Vet Center - Outstation node updates complete. {$sandbox['current']} / {$sandbox['total']} - Total updated: {$sandbox['updated']}\n";
  }

  return "Processed nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
}
