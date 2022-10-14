#!/usr/bin/env drush
<?php

/**
 * @file
 * Load up all existing nodes and re-save to fix carriage returns.
 */

/**
 * Log a message.
 *
 * @param string $message
 *   The message to log.
 */
function log_message(string $message): void {
  // \Drupal::logger(__FILE__)->notice($message);
  echo PHP_EOL . $message;
}

/**
 * Get memory usage, formatted.
 *
 * @return string
 *   Formatted memory usage.
 */
function get_memory_usage() {
  $bytes_used = memory_get_usage(TRUE);
  if ($bytes_used < 1024) {
    return $bytes_used . 'B';
  }
  elseif ($bytes_used < 1048576) {
    return round($bytes_used / 1024, 2) . 'KB';
  }
  return round($bytes_used / (1024 * 1024), 2) . 'MB';
}

/**
 * Process a chunk of nodes.
 *
 * @param int $chunk_id
 *   Chunk index.
 * @param array $chunk
 *   Array of nodes.
 */
function process_chunk(int $chunk_id, array $chunk) {
  $entity_type_manager = \Drupal::entityTypeManager();
  $node_storage = $entity_type_manager->getStorage('node');
  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = $node_storage->loadMultiple($chunk);
  $count = count($nodes);
  log_message("Loaded {$count} nodes as chunk {$chunk_id}");
  foreach ($nodes as $nid => $node) {
    // Do not do this processing if there is a forward draft.
    $default_revision = $node->getRevisionId();
    $latest_revision = $node_storage->getLatestRevisionId($nid);
    if ($latest_revision == $default_revision) {
      $time = time();
      // Make this change a new revision.
      $node->setNewRevision(TRUE);
      $node->setRevisionUserId(1317);
      $node->setChangedTime($time);
      $node->setRevisionCreationTime($time);
      $node->setRevisionLogMessage('Saved to fix carriage returns in wysiwyg.');
      $node->setSyncing(TRUE);
      $node->save();
    }
    else {
      log_message("https://prod.cms.va.gov/node/{$nid} may have a forward draft; latest revision {$latest_revision} is different than {$node_storage->getLatestRevisionId($nid)}. Skipping.");
    }
  }
}

/**
 * Process a content type.
 *
 * @param string $content_type
 *   The content type to process.
 */
function process_content_type(string $content_type) {
  $nids = \Drupal::entityQuery('node')
    ->condition('type', $content_type)
    ->condition('status', '1')
    ->execute();
  $nid_count = count($nids);
  log_message("Processing content type {$content_type}...");
  log_message("Found {$nid_count} nodes...");
  $chunks = array_chunk($nids, 50);

  foreach ($chunks as $chunk_id => $chunk) {
    process_chunk($chunk_id, $chunk);
  }
}

/*$content_types = [
'health_care_region_detail_page',
'campaign_landing_page',
'page',
'vet_center',
'office',
'news_story',
'press_release',
'full_width_banner_alert',
'health_care_local_facility',
'event',
'q_a',
'press_releases_listing',
'vet_center_facility_health_servi',
'vamc_system_policies_page',
'vamc_operating_status_and_alerts',
'regional_health_care_service_des',
'person_profile',
];*/

error_reporting(E_ERROR | E_PARSE);

$entity_type_manager = \Drupal::entityTypeManager();
$node_storage = $entity_type_manager->getStorage('node');
$user_storage = $entity_type_manager->getStorage('user');

$uid = 1317;
$user = $user_storage->load($uid);
\Drupal::service('account_switcher')->switchTo($user);
log_message("Acting as {$user->getDisplayName()} [{$uid}]");

$start_time = time();
$content_type = $_SERVER['argv'][3];
process_content_type($_SERVER['argv'][3]);

$now = time();
$elapsed_time = $now - $start_time;

log_message("Finished content type {$content_type} in {$elapsed_time} seconds.");
log_message('Memory usage: ' . get_memory_usage());
