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

error_reporting(E_ERROR | E_PARSE);
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
$entity_type_manager = \Drupal::entityTypeManager();
$node_storage = $entity_type_manager->getStorage('node');
$user_storage = $entity_type_manager->getStorage('user');

$uid = 1317;
$user = $user_storage->load($uid);
\Drupal::service('account_switcher')->switchTo($user);
log_message("Acting as {$user->getDisplayName()} [{$uid}]");

$linkies = \Drupal::entityQuery('linky')->execute();
$linky_count = count($linkies);
log_message("Found {$linky_count} existing linkies...");

$start_time = time();

foreach ($content_types as $content_type) {

  $segment_start_time = time();

  $nids = \Drupal::entityQuery('node')
    ->condition('type', $content_type)
    ->condition('status', '1')
    ->execute();
  $nid_count = count($nids);
  log_message("Processing content type {$content_type}...");
  log_message("Found {$nid_count} nodes...");

  $chunks = array_chunk($nids, 50);
  foreach ($chunks as $chunk_id => $chunk) {
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $node_storage->loadMultiple($chunk);
    $count = count($nodes);
    log_message("Loaded {$count} nodes as chunk {$chunk_id}");
    foreach ($nodes as $nid => $node) {
      // Make this change a new revision.
      $node->setNewRevision(TRUE);
      $node->setRevisionUserId(1317);
      $node->setChangedTime(time());
      $node->setRevisionCreationTime(time());
      $node->setRevisionLogMessage('Saved to create linkies where applicable');
      $node->setSyncing(TRUE);
      $node->save();
    }
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
