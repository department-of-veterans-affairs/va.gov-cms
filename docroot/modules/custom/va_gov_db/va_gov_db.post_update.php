<?php

/**
 * @file
 * Post update file for VA Gov DB.
 */

use Psr\Log\LogLevel;

/**
 * Re-save all VAMC system & facility health service nodes.
 */
function va_gov_db_post_update_resave_facility_nodes(&$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for system/facility health service nodes.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $group = $query
      ->orConditionGroup()
      ->condition('type', 'health_care_local_health_service')
      ->condition('type', 'regional_health_care_service_des');
    $result_count = $query
      ->condition($group)
      ->count()
      ->execute();
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return t('No health service nodes were found to be processed.');
  }

  $limit = 25;
  // Fetch node ids for processing in the current batch run.
  $query = $node_storage->getQuery();
  $group = $query
    ->orConditionGroup()
    ->condition('type', 'health_care_local_health_service')
    ->condition('type', 'regional_health_care_service_des');
  $node_ids = $query
    ->condition($group)
    ->range($sandbox['current'], $limit)
    ->execute();

  // Load entities.
  $nodes = $node_storage->loadMultiple($node_ids);
  $nids = [];

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
    $nids[] = $node->id();
    $sandbox['current']++;
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
    return t('Health service node re-saving complete.');
  }

  return t('Processing health service nodes...');
}
