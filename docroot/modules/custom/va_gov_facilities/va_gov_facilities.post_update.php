<?php

/**
 * @file
 * Post update file for VA Facilities.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Psr\Log\LogLevel;

/**
 * Move facility service location data to paragraphs.
 */
function va_gov_db_post_update_move_service_location_to_paragraphs(&$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for facility health service nodes.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $group = $query
      ->orConditionGroup()
      ->condition('type', 'health_care_local_health_service');

    $nids_to_update = $query
      ->condition($group)->accessCheck(FALSE)->execute();
    $result_count = count($nids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    $sandbox['nids_to_update'] = array_combine(
            array_map('_va_gov_db_stringifynid', array_values($nids_to_update)),
            array_values($nids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return t('No health service nodes were found to be processed.');
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $facility_health_service_nodes = $node_storage->loadMultiple($node_ids);

  foreach ($facility_health_service_nodes as $facility_health_service_node) {
    // Gather existing service locations.
    $service_locations = $facility_health_service_node->get('field_service_location')->referencedEntities();;
    foreach ($service_locations as $service_location) {
      _migrate_address();
      _migrate_appointment_into_text();
      _migrate_appointment_phone_number();
      _migrate_contact_info();
      _migrate_hours();
      _migrate_schedule_online();
      _migrate_walkins_accepted();
      // Save the paragraph as a new revision.
      $service_location->setNewRevision(TRUE);
      // @todo I think I need to grab a new revision id for the node save.
     // put this into the node field'target_revision_id' => $service_location->getRevisionId(),
     //$service_location->set('field_fieldname1', 'some value');
      $service_location->save();

    }

    // Make this change a new revision.
    $facility_health_service_node->setNewRevision(TRUE);

    // Set revision author to uid 1317 (CMS Migrator user).
    $facility_health_service_node->setRevisionAuthorId(1317);
    $facility_health_service_node->setChangedTime(time());
    $facility_health_service_node->setRevisionCreationTime(time());
    $facility_health_service_node->setOwnerId(1317);

    // Set revision log message.
    $facility_health_service_node->setRevisionLogMessage('Resaved node to update title and path alias.');
    $facility_health_service_node->save();
    unset($sandbox['nids_to_update']["node_{$facility_health_service_node->id()}"]);
    $nids[] = $facility_health_service_node->id();
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
    return "Health service node re-saving complete. {$sandbox['current']} / {$sandbox['total']}";
  }

  return "Processing health service nodes...{$sandbox['current']} / {$sandbox['total']}";
}

function _migrate_address(){
  // Moving from:
  // Moving to: service_location -> field_service_location_address

}

  function _migrate_appointment_into_text(){
    // Moving from:
    // Moving to: paragraph.service_location.field_appointment_intro_text

    // Needs a bifurcation for VAMC vs VBA.
  }

  function _migrate_appointment_phone_number() {
    // Moving from: field_phone_numbers_paragraph ??
    // Moving to: field_phone (paragraphs)
    // and: field_use_main_facility_phone (boolean)
  }

  function _migrate_contact_info() {
    // Moving from:
    // Moving to: service_location ->field_email_contacts.
  }

  function _migrate_hours() {
    // Moving from:
    // Moving to: service_location ->field_office_hours (Office hours field).
    // and: service_location ->field_hours (list).
  }

function _migrate_schedule_online($option) {
  // Moving from: field_online_scheduling_availabl
  // Moving to:
  $schedule_online_map = [
    // schedule online => service location schedule online
    'yes' => 'yes',
    'no' => 'no',
    // This is the do no harm, option defaulting to most restrictive.
    // 'unspecified' => '??? @todo is this needed',
  ];

  return $schedule_online_map[$option];
}

function _migrate_walkins_accepted($option) {
  // Moving from: 	node field_walk_ins_accepted
  // Moving to:
  $walkins_accepted_map = [
    // walkins accepted => office visits
    'yes' => 'yes with or without an appointment',
    'no' => 'yes by appointment only',
    // This is the do no harm, option defaulting to most restrictive.
    'unspecified' => 'yes by appointment only',
  ];

  return $walkins_accepted_map[$option];
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
function _va_gov_facilities_stringifynid($nid) {
  return "node_$nid";
}
