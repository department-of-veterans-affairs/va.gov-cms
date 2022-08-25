#!/usr/bin/env drush
<?php

/**
 * @file
 * Load up all existing nodes and create a bunch of Linky entities.
 */

const TRACK_LINKIES = FALSE;

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
  $entity_type_manager = \Drupal::entityTypeManager();
  $node_storage = $entity_type_manager->getStorage('node');
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

/**
 * Process a content type.
 *
 * @param string $content_type
 *   The content type to process.
 * @param array $nid_allow_lists
 *   List of nids that can be modified for specified content types.
 * @param int $previous_linky_count
 *   Number of linkies created so far.
 */
function process_content_type(string $content_type, array $nid_allow_lists, int $previous_linky_count) {
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
  $linky_count = $previous_linky_count;

  foreach ($chunks as $chunk_id => $chunk) {
    $previous_linky_count = process_chunk($chunk_id, $chunk, $previous_linky_count);
  }
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
  'regional_health_care_service_des' => [
    346,
    3187,
    3190,
    4709,
    4992,
    5252,
    5297,
    5543,
    6433,
    7339,
    7348,
    7389,
    7402,
    7416,
    7465,
    7482,
    7503,
    7525,
    7532,
    7533,
    7579,
    9891,
    9920,
    9934,
    9981,
    10074,
    10076,
    10082,
    10084,
    10146,
    10154,
    10174,
    10224,
    10323,
    10569,
    10574,
    10575,
    10585,
    10594,
    10595,
    10629,
    10637,
    10654,
    10725,
    10890,
    10947,
    10953,
    10955,
    15215,
    15224,
    15235,
    15239,
    15244,
    15268,
    15301,
    17787,
    17935,
    20347,
    20880,
    20904,
    21079,
    21324,
    21336,
    23740,
    23990,
    24035,
    24067,
    24152,
    24174,
    24180,
    24206,
    24218,
    24239,
    24246,
    24411,
    24427,
    24444,
    24503,
    24569,
    24693,
    27403,
    27407,
    27893,
    28353,
    28467,
    28480,
    28487,
    28489,
    28499,
    28500,
    28502,
    28503,
    28504,
    28506,
    28509,
    28514,
    28782,
    28825,
    29025,
    29038,
    29105,
    29111,
    29112,
    29118,
    29123,
    29133,
    29135,
    29335,
    29344,
    29353,
    29366,
    29368,
    29370,
    29378,
    29424,
    29425,
    29624,
    29679,
    29697,
    29857,
    34027,
    34842,
    35949,
    36219,
    39180,
    39910,
    42408,
    42445,
    43013,
    43026,
    43193,
    43338,
    44026,
    44119,
    44248,
    44293,
    44462,
    44758,
    44761,
    44921,
    45228,
    46299,
    46661,
    47876,
    48348,
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

$start_time = time();

$linky_count = process_content_type($_SERVER['argv'][3], $nid_allow_lists, $linky_count);

$now = time();

$linkies = \Drupal::entityQuery('linky')->execute();
$new_linky_count = count($linkies);
$linky_count_difference = $new_linky_count - $previous_linky_count;
$elapsed_time = $now - $start_time;
$linky_rate = $linky_count_difference / $elapsed_time;

log_message("Finished content type {$content_type}... {$linky_count_difference} new linky entities added in {$elapsed_time} seconds ({$linky_rate} links/second)");
log_message('Memory usage: ' . get_memory_usage());
