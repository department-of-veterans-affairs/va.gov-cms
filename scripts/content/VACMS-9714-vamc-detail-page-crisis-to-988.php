<?php

/**
 * @file
 * Replace 800-273-8255 with 988 for VAMC System Operating Status.
 *
 * VACMS-9714-vamc-detail-page-crisis-to-988.php.
 */

require_once __DIR__ . '/script-library.php';

use Psr\Log\LogLevel;

$revision_message = 'Updated Crisis number from 800-273-8255 to 988';
$sandbox = ['#finished' => 0];
do {
  print(va_gov_change_crisis_hotline_to_988_nodes($sandbox, $revision_message));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Replace 800-273-8255 with 988.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param string $revision_message
 *   Text to be used in revision log message.
 *
 * @return string
 *   Status message.
 */
function va_gov_change_crisis_hotline_to_988_nodes(array &$sandbox, $revision_message) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for VAMC Detail Page nodes that are not archived.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $query->condition('type', 'health_care_region_detail_page');
    $query->condition('moderation_state', 'archived', '!=');
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
    return "No VAMC Detail Page nodes found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);
  foreach ($nodes as $node) {
    /** @var \Drupal\node\NodeInterface $node */
    $nid = $node->id();
    $nvid = $node->getRevisionId();
    $old_value = $node->get('field_description')->value;
    $new_value = normalize_crisis_number($old_value, TRUE);

    // If the value was altered then update the node.
    if ($new_value !== $old_value && !empty($new_value)) {
      $node->field_description->setValue($new_value);

      // Grab the latest revision before we save this one.
      $latest_revision = get_node_at_latest_revision($nid);
      save_node_revision($node, $revision_message);

      // If a revision (draft) newer than the default exists, update it as well.
      if ($nvid !== $latest_revision->getRevisionId()) {
        $old_revision_value = $latest_revision->get('field_description')->value;
        $new_revision_value = normalize_crisis_number($old_revision_value, TRUE);

        if ($new_revision_value !== $old_revision_value && !empty($new_revision_value)) {
          $latest_revision->field_description->setValue($new_revision_value);
          save_node_revision($latest_revision, $revision_message);
          unset($latest_revision);
        }
      }

      $sandbox['updated']++;
      $nids[] = $nid;
    }

    unset($sandbox['nids_to_update']["node_{$nid}"]);
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  if (!empty($nids)) {
    Drupal::logger('va_gov_db')
      ->log(LogLevel::INFO, 'VAMC Detail Page nodes: %current nodes phone numbers updated. Nodes updated: %nids', [
        '%current' => $sandbox['current'],
        '%nids' => empty($nids) ? 'None' : implode(', ', $nids),
      ]);
  }

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Processing all %count VAMC Detail Page nodes completed', [
      '%count' => $sandbox['total'],
    ]);
    return "VAMC Detail Page node updates complete. {$sandbox['current']} / {$sandbox['total']} - Total updated: {$sandbox['updated']}\n";
  }

  return "Processed nodes... {$sandbox['current']} / {$sandbox['total']}.\n";
}
