<?php

/**
 * @file
 * Remove data from supplemental status and supplemental status more info.
 *
 * These status fields have been used for COVID-19.
 *
 * VACMS-13679-wipe-out-covid-status-data.php.
 */

require_once __DIR__ . '/script-library.php';

use Psr\Log\LogLevel;

$revision_message = 'In a separate action, COVID-19 status and details were removed via a script.';
$sandbox = ['#finished' => 0];
do {
  print(va_gov_remove_covid_status_data($sandbox, $revision_message));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Remove COVID-19 status data.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param string $revision_message
 *   Text to be used in revision log message.
 *
 * @return string
 *   Status message.
 */
function va_gov_remove_covid_status_data(array &$sandbox, $revision_message) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the count for VAMC Facilities.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $query->condition('type', 'health_care_local_facility');
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
    return "No VAMC Facility nodes found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);
  foreach ($nodes as $node) {
    /** @var \Drupal\node\NodeInterface $node */
    $nid = $node->id();
    $nvid = $node->getRevisionId();
    $node->field_supplemental_status->target_id = NULL;
    $node->field_supplemental_status_more_i->value = "";

    // Grab the latest revision before we save this one.
    $latest_revision = get_node_at_latest_revision($nid);
    save_node_revision($node, $revision_message, FALSE);

    // If a revision (draft) newer than the default exists, update it as well.
    if ($nvid !== $latest_revision->getRevisionId()) {
      $latest_revision->field_supplemental_status->target_id = NULL;
      $latest_revision->field_supplemental_status_more_i->value = "";
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
    ->log(LogLevel::INFO, 'VAMC Facility nodes: %current supplemental status (COVID-19) field pairs updated. Nodes updated: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => empty($nids) ? 'None' : implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Processing all %count VAMC Facilities completed', [
      '%count' => $sandbox['total'],
    ]);
    return "VAMC Facility node updates complete. {$sandbox['current']} / {$sandbox['total']} - Total updated: {$sandbox['updated']}\n";
  }

  return "Processed nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
}
