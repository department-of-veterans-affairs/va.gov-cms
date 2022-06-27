<?php

/**
 * @file
 * Output comments for all office hours fields to a CSV.
 *
 * VACMS-9358-audit-opening-hours-comments.php.
 */

use Psr\Log\LogLevel;

// Begin processing.
$sandbox['#nodes_finished'] = 0;
$sandbox['#paragraphs_finished'] = 0;
$audit_data[] = '"Node ID","Content Type","Comment","URL"';

do {
  print(va_gov_audit_node_office_hours_comments($sandbox, $audit_data));
} while ($sandbox['#nodes_finished'] < 1);

do {
  print(va_gov_audit_paragraph_office_hours_comments($sandbox, $audit_data));
} while ($sandbox['#paragraphs_finished'] < 1);

// Paragraph processing complete - write to audit file.
$file_name = 'office_hours_comment_audit.csv';

// Open csv file for auditing outliers.
$audit_file = fopen($file_name, 'w');
if ($audit_file === FALSE) {
  die('Error opening the file ' . $file_name);
}

foreach ($audit_data as $audit_row) {
  fwrite($audit_file, $audit_row . "\n");
}

fclose($audit_file);

return;

/**
 * Audit office hours fields for nodes.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param array $audit_data
 *   Array for storing data outliers so we can report on them.
 *
 * @return string
 *   Status message.
 */
function va_gov_audit_node_office_hours_comments(array &$sandbox, array &$audit_data) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for nodes with office hours.
  if (!isset($sandbox['node_total'])) {
    $node_types = [
      'health_care_local_facility',
      'vet_center',
      'vet_center_cap',
      'vet_center_mobile_vet_center',
      'vet_center_outstation',
      'vamc_system_billing_insurance',
    ];

    $node_query = $node_storage->getQuery();
    $nids_to_update = $node_query->condition('type', $node_types, 'IN')->execute();
    $result_count = count($nids_to_update);
    $sandbox['node_total'] = $result_count;
    $sandbox['node_current'] = 0;

    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['nids_to_update'] = array_combine(
      array_map('_va_gov_stringifynid', array_values($nids_to_update)),
      array_values($nids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['node_total'])) {
    $sandbox['#nodes_finished'] = 1;
    return "No nodes found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);
  foreach ($nodes as $node) {
    $hours_field_name = ($node->bundle() === 'vamc_system_billing_insurance') ? 'field_hours_for_copay_inquiries_' : 'field_office_hours';

    // Grab the value for the office hours field.
    $office_hours = $node->get($hours_field_name);

    // Check the sub field (comments) to see if they have content.
    foreach ($office_hours as $day) {
      $comment = $day->comment;
      if (!empty($comment)) {
        $prefix = "https://prod.cms.va.gov";
        $new_line = '"' . $node->id() . '","' . $node->type->entity->label() . '","' . $comment . '","' . $prefix . $node->toUrl()->toString() . '"';
        if (!in_array($new_line, $audit_data)) {
          $audit_data[] = $new_line;
        }
      }
    }

    unset($sandbox['nids_to_update'][_va_gov_stringifynid($node->id())]);
    $nids[] = $node->id();
    $sandbox['node_current'] = $sandbox['node_total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Nodes: %current nodes hours comments audited. Nodes processed: %nids', [
      '%current' => $sandbox['node_current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#nodes_finished'] = ($sandbox['node_current'] / $sandbox['node_total']);

  // Log the all-finished notice.
  if ($sandbox['#nodes_finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Auditing all %count nodes completed by VACMS-9358-audit-opening-hours-comments', [
      '%count' => $sandbox['node_total'],
    ]);
    return "Node audit complete. {$sandbox['node_current']} / {$sandbox['node_total']}\n";
  }

  return "Audited nodes... {$sandbox['node_current']} / {$sandbox['node_total']}.\n";

}

/**
 * Audit office hours fields for paragraphs.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param array $audit_data
 *   Array for storing data outliers so we can report on them.
 *
 * @return string
 *   Status message.
 */
function va_gov_audit_paragraph_office_hours_comments(array &$sandbox, array &$audit_data) {
  $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the Service Location paragraph count. This runs only once.
  if (!isset($sandbox['paragraph_total'])) {
    $paragraph_query = $paragraph_storage->getQuery();
    $pids_to_update = $paragraph_query->condition('type', 'service_location')->execute();
    $result_count = count($pids_to_update);
    $sandbox['paragraph_total'] = $result_count;
    $sandbox['paragraph_current'] = 0;

    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['pids_to_update'] = array_combine(
      array_map('_va_gov_stringifypid', array_values($pids_to_update)),
      array_values($pids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['paragraph_total'])) {
    $sandbox['#paragraphs_finished'] = 1;
    return "No service location paragraphs were found to be processed.\n";
  }

  $limit = 25;

  // Load entities.
  $paragraph_ids = array_slice($sandbox['pids_to_update'], 0, $limit, TRUE);
  $service_locations = $paragraph_storage->loadMultiple($paragraph_ids);

  foreach ($service_locations as $service_location) {
    // Grab the parent node for this paragraph.
    $node = $service_location->getParentEntity();

    // Grab the value for the office hours field.
    $office_hours = $service_location->get('field_office_hours');

    // Check the sub field (comments) to see if they have content.
    foreach ($office_hours as $day) {
      $comment = $day->comment;
      if (!empty($comment)) {
        $prefix = "https://prod.cms.va.gov";
        $new_line = '"' . $node->id() . '","' . $node->type->entity->label() . '","' . $comment . '","' . $prefix . $node->toUrl()->toString() . '"';
        if (!in_array($new_line, $audit_data)) {
          $audit_data[] = $new_line;
        }
      }
    }

    unset($sandbox['pids_to_update'][_va_gov_stringifypid($service_location->id())]);
    $pids[] = $service_location->id();
    $sandbox['paragraph_current'] = $sandbox['paragraph_total'] - count($sandbox['pids_to_update']);

  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Service Location: %current paragraphs office hours audited. Paragraphs processed: %pids', [
      '%current' => $sandbox['paragraph_current'],
      '%pids' => implode(', ', $pids),
    ]);

  $sandbox['#paragraphs_finished'] = ($sandbox['paragraph_current'] / $sandbox['paragraph_total']);

  // Log the all-finished notice.
  if ($sandbox['#paragraphs_finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count nodes completed by va_gov_force_save_remove_timezones_in_comments', [
      '%count' => $sandbox['paragraph_total'],
    ]);
    return "Paragraph audit complete. {$sandbox['paragraph_current']} / {$sandbox['paragraph_total']}\n";
  }

  return "Processed paragraphs... {$sandbox['paragraph_current']} / {$sandbox['paragraph_total']}.\n";

}

/**
 * Callback function to concat paragraph ids with string.
 *
 * @param int $pid
 *   The paragraph id.
 *
 * @return string
 *   The paragraph id appended to the end of paragraph_.
 */
function _va_gov_stringifypid($pid) {
  return "paragraph_$pid";
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
