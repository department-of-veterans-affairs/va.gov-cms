#!/usr/bin/env drush
<?php

/**
 * @file
 * Load up all existing nodes and create a bunch of Linky entities.
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
 * @param int $previous_linky_count
 *   The count of linky items before.
 *
 * @return int
 *   The new count of linky items.
 */
function process_chunk(int $chunk_id, array $chunk, int $previous_linky_count) {
  static $node_storage;
  if (!$node_storage) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $node_storage = $entity_type_manager->getStorage('node');
  }
  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = $node_storage->loadMultiple($chunk);
  $count = count($nodes);
  log_message("Loaded {$count} nodes as chunk {$chunk_id}");
  foreach ($nodes as $nid => $node) {
    $time = time();
    // Make this change a new revision.
    $node->setNewRevision(TRUE);
    $node->setRevisionUserId(1317);
    $node->setChangedTime($time);
    $node->setRevisionCreationTime($time);
    $node->setRevisionLogMessage('Saved to create linkies where applicable');
    $node->setSyncing(TRUE);
    $node->save();

    if (TRACK_LINKIES) {
      $now_linkies = \Drupal::entityQuery('linky')->execute();
      $now_linky_count = count($now_linkies);
      $now_linky_count_diff = $now_linky_count - $previous_linky_count;
      if ($now_linky_count_diff > 0) {
        log_message("Created {$now_linky_count_diff} new linkies updating node ${nid}...");
        $previous_linky_count = $now_linky_count;
      }
    }

  }
  return $previous_linky_count;
}

$content_types = [
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
];

$nid_allow_lists = [
  'health_care_region_detail_page' => [
    2761,
    2768,
    2771,
    2777,
    6279,
    6291,
    6513,
    7084,
    7134,
    7147,
    7151,
    7162,
    7681,
    7811,
    8038,
    8044,
    8086,
    8154,
    8172,
    8188,
    8200,
    8274,
    8455,
    9317,
    9543,
    16713,
    18280,
    37218,
  ],
];

error_reporting(E_ERROR | E_PARSE);

$entity_type_manager = \Drupal::entityTypeManager();
$node_storage = $entity_type_manager->getStorage('node');
$user_storage = $entity_type_manager->getStorage('user');

$uid = 1317;
$user = $user_storage->load($uid);
\Drupal::service('account_switcher')->switchTo($user);
log_message("Acting as {$user->getDisplayName()} [{$uid}]");

$linkies = \Drupal::entityQuery('linky')->execute();
$linky_count = count($linkies);
$previous_linky_count = $linky_count;
log_message("Found {$linky_count} existing linkies...");

const TRACK_LINKIES = FALSE;

$start_time = time();

$segment_start_time = time();

foreach ($content_types as $content_type) {
  if (isset($nid_allow_lists[$content_type])) {
    $nids = $nid_allow_lists[$content_type];
  }
  else {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', $content_type)
      ->condition('status', '1')
      ->execute();
  }
  $nid_count = count($nids);
  log_message("Processing content type {$content_type}...");
  log_message("Found {$nid_count} nodes...");
  $chunks = array_chunk($nids, 50);

  foreach ($chunks as $chunk_id => $chunk) {
    $previous_linky_count = process_chunk($chunk_id, $chunk, $previous_linky_count);
    log_message('Memory usage: ' . get_memory_usage());
  }

  $now = time();

  $linkies = \Drupal::entityQuery('linky')->execute();
  $new_linky_count = count($linkies);
  $linky_count_difference = $new_linky_count - $linky_count;
  $linky_count = $new_linky_count;

  $total_elapsed_time = $now - $start_time;
  $elapsed_time = $now - $segment_start_time;

  $linky_rate = $linky_count_difference / $elapsed_time;
  $total_linky_rate = $linky_count / $total_elapsed_time;
  log_message("Finished content type {$content_type}... {$linky_count_difference} new linky entities added in {$elapsed_time} seconds ({$linky_rate} links/second; total: {$linky_count} links in {$total_elapsed_time} seconds, {$total_linky_rate} links/second).");
}
