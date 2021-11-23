<?php

/**
 * @file
 * Creates service locations for facility health services that don't have one.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Psr\Log\LogLevel;

$sandbox = ['#finished' => 0];
do {
  print(update_health_health_service_nodes($sandbox));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Get an array of Facility Health Service nids without location.
 *
 * @return array
 *   The node ids.
 */
function get_health_services_without_location() {
  $database = \Drupal::database();

  // Get all facility health care nodes that don't have a service location.
  $nids_without_location = $database->select('node', 'n');
  $nids_without_location->leftJoin('node__field_service_location', 'nfsl', 'n.nid = nfsl.entity_id');
  $nids_without_location->condition('n.type', 'health_care_local_health_service', '=');
  $nids_without_location->condition('nfsl.entity_id', NULL, 'IS NULL');
  $nids_without_location->fields('n', ['nid']);
  $nids_without_location_array = $nids_without_location->execute()->fetchCol();

  return $nids_without_location_array;
}

/**
 * Creates the service location address paragraph for the service location.
 *
 * @param object $service_paragraph
 *   The service paragraph.
 */
function create_service_location_address_paragraph($service_paragraph) {
  // Create the service location address paragraph that will be tied to
  // the service location parent paragraph.
  $address_paragraph = Paragraph::create([
    'type' => 'service_location_address',
    'field_use_facility_address' => [
      'value' => '1',
    ],
  ]);
  $address_paragraph->save();
  $service_paragraph->field_service_location_address->appendItem($address_paragraph);
  $service_paragraph->save();
}

/**
 * Creates the service location paragraph for the health service node.
 *
 * @return object
 *   The service loaction paragraph with nested service address paragraph.
 */
function create_service_location_paragraph() {
  // Create the service location paragraph that will be tied to the health
  // service node, and attach the child service location address paragraph to
  // it.  We don't need to create child paragraphs for hours and phone, b/c
  // the only fields we need to set for said items live on this paragraph.
  $service_paragraph = Paragraph::create([
    'type' => 'service_location',
    'field_hours' => [
      "value" => '0',
    ],
    'field_use_main_facility_phone' => [
      'value' => '1',
    ],
  ]);
  // Save our new service location paragraph.
  $service_paragraph->save();
  // Now attach our child service location address paragraph.
  create_service_location_address_paragraph($service_paragraph);

  return $service_paragraph;
}

/**
 * Attaches the service locations to the health service nodes.
 */
function update_health_health_service_nodes(&$sandbox) {
  // Now grab all of our service location-less health service nodes.
  if (!isset($sandbox['total'])) {
    $service_nodes = get_health_services_without_location();
    // Set up our batch process.
    $result_count = count($service_nodes);

    $sandbox['total'] = $result_count;
    $sandbox['nids_to_update'] = array_combine(
      array_map('_va_gov_stringifynid', array_values($service_nodes)),
      array_values($service_nodes));
    $sandbox['#finished'] = 0;
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    // Log the all-finished notice.
    Drupal::logger('drush_scr')->log(LogLevel::INFO, 'Attaching service locations to health service nodes completed by drush script.', [
      '%count' => $sandbox['total'],
    ]);
    return "Health service node re-saving complete.\n";
  }

  $limit = 50;
  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $nodes = $node_storage->loadMultiple($node_ids);

  // Run through them, and attach the service location paragraph (and child)
  // to each.
  foreach ($nodes as $node) {
    $paragraph = create_service_location_paragraph();
    $node->field_service_location->appendItem($paragraph);
    // Make this change a new revision.
    /** @var \Drupal\node\NodeInterface $node */
    $node->setNewRevision(TRUE);
    // Set revision user to uid 1317 (CMS Migrator user).
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    // Revision log message.
    $node->setRevisionLogMessage('Resaved node to add service location.');
    $node->save();

    // Node has been processed, take it out of the sandbox.
    // unset($sandbox['nids_to_update'][$node->id()]);
    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }
  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Facility health service nodes %current nodes updated. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('drush_scr')->log(LogLevel::INFO, 'Processing all %count health service nodes completed by drush script.', [
      '%count' => $sandbox['total'],
    ]);
    return "Facility health service node processing complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processing facility health service nodes...{$sandbox['current']} / {$sandbox['total']}\n";

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
