<?php

/**
 * @file
 * Common code related to drupal content scripts.
 */

use Drupal\node\NodeInterface;
use Drupal\Core\Queue\QueueInterface;

require_once dirname(dirname(__FILE__)) . '/script-library.php';

define('CMS_QUEUE_NAME', 'vacms_11497');
define('CMS_CHUNK_LENGTH', 50);
define('CMS_OUTPUT_HEADER', "%-6s | %-7s | %-7s | %-7s | %-5s | %-5s | %-19s | %-19s | %-19s | %-19s | %-19s | %-40s |\n");
define('CMS_OUTPUT_SEPARATOR', "%'--6s | %'--7s | %'--7s | %'--7s | %'--5s | %'--5s | %'--19s | %'--19s | %'--19s | %'--19s | %'--19s | %'--40s |\n");
define('CMS_EXCLUSION_USER_IDS', [
  0,
  1,
  CMS_MIGRATOR_ID,
]);

/**
 * Was node revision touched by a human?
 *
 * @param \Drupal\node\NodeInterface $node
 *   The node in question.
 *
 * @return bool
 *   TRUE if this revision was authored by a "human".
 */
function is_node_revision_authored_by_human(NodeInterface $node): bool {
  return !in_array($node->getRevisionUserId(), CMS_EXCLUSION_USER_IDS);
}

/**
 * Calculate a human-last-touched-date for a revision.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The revision in question.
 *
 * @return int
 *   The timestamp of the last human touch (might be 0).
 */
function get_revision_last_human_date(NodeInterface $node): int {
  $result = get_latest_human_field_value($node);
  $result = $result ?: (is_node_revision_authored_by_human($node) ? $node->getRevisionCreationTime() : 0);
  return $result;
}

/**
 * Retrieve the last-human-touched field value.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The revision in question.
 *
 * @return int
 *   The timestamp of the last human touch (might be 0).
 */
function get_latest_human_field_value(NodeInterface $node): int {
  return $node->get('field_last_saved_by_an_editor')->getValue()[0]['value'] ?: 0;
}

/**
 * Calculate a human-last-touched-date for a revision.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The revision in question.
 * @param int $revision_id_limit
 *   The most recent revision ID that can be considered, or NULL to disable.
 *
 * @return int
 *   The timestamp of the last human touch (might be 0).
 */
function get_node_last_human_date(NodeInterface $node, int $revision_id_limit = NULL): int {
  $revision_id_limit = $revision_id_limit ?? 99999999;
  $database = \Drupal::database();
  $query = $database->select('node_revision');
  $query->condition('nid', $node->id());
  $query->condition('vid', $revision_id_limit, '<=');
  $query->condition('revision_uid', CMS_EXCLUSION_USER_IDS, 'NOT IN');
  $query->addField('node_revision', 'revision_timestamp');
  $query->addExpression('MAX("revision_timestamp")', 'revision_timestamp');
  $result = $query->execute()->fetchField(1);
  return (int) $result;
}

/**
 * Format the timestamp for output.
 */
function format_time(int $timestamp): string {
  return $timestamp ? date('Y-m-d H:i:s', $timestamp) : '(not set)';
}

/**
 * Format a boolean for output.
 */
function format_bool(bool $condition): string {
  return $condition ? 'Yes' : 'No';
}

/**
 * Print some information about a node or node revision.
 */
function print_header(): void {
  printf(CMS_OUTPUT_HEADER, 'NID', 'VID', 'Publish', 'Default', 'UID', 'Human', 'Created', 'Touched (Set)', 'Touched (Calc)', 'Touched (Node)', 'Touched (Will Set)', 'Title');
  printf(CMS_OUTPUT_SEPARATOR, '', '', '', '', '', '', '', '', '', '', '', '', '', '');
}

/**
 * Print some information about a node or node revision.
 *
 * @param \Drupal\node\NodeInterface $node
 *   A valid node object to print.
 */
function print_node(NodeInterface $node): void {
  // O(1).
  $last_human_date = get_latest_human_field_value($node);
  // O(1).
  $revision_date = get_revision_last_human_date($node);
  // O(1) or O(n) depending on version.
  $node_date = get_node_last_human_date($node, $node->getRevisionId());
  $set_date = ($node->isDefaultRevision() || $node->isLatestRevision()) ? $node_date : 0;
  printf(CMS_OUTPUT_HEADER,
    $node->id(),
    $node->getRevisionId(),
    format_bool($node->isPublished()),
    format_bool($node->isDefaultRevision()),
    $node->getRevisionUserId(),
    format_bool(is_node_revision_authored_by_human($node)),
    format_time($node->getRevisionCreationTime()),
    format_time($last_human_date),
    format_time($revision_date),
    format_time($node_date),
    format_time($set_date),
    substr($node->getTitle(), 0, 40)
  );
}

/**
 * Print all meaningful information about a node or node revision.
 *
 * @param \Drupal\node\NodeInterface $node
 *   A valid node object to print.
 */
function print_full_history_of_node(NodeInterface $node): void {
  foreach (get_node_all_revisions($node->id()) as $revision) {
    print_node($revision);
  }
}

/**
 * Set a node's human-last-touched date on the specific revision.
 *
 * @param \Drupal\node\NodeInterface $node
 *   A valid node object to print.
 * @param int $timestamp
 *   The timestamp to set.
 */
function set_revision_last_human_date(NodeInterface $node, int $timestamp): void {
  // We should only be altering the latest revision and the default revision,
  // if it is distinct.
  if (!$node->isLatestRevision() && !$node->isDefaultRevision()) {
    debug_log_message("This revision is neither the latest nor the default revision, so we ignore it.");
    return;
  }
  // If this draft is the default and it's not published, then we're doing
  // something weird.  We should only be running this (at this time) on nodes
  // with at least one published revision.
  if (!$node->isPublished() && $node->isDefaultRevision()) {
    debug_log_message("The default revision is not published, so we ignore it.");
    return;
  }

  $node->set('field_last_saved_by_an_editor', $timestamp);

  save_node_existing_revision_without_log($node);
}

/**
 * Calculate and set date on the specific revision.
 *
 * @param \Drupal\node\NodeInterface $revision
 *   A valid node revision to alter.
 */
function update_revision(NodeInterface $revision, bool $force = FALSE): void {
  $current = get_latest_human_field_value($revision);
  $revision_id = $revision->getRevisionId();
  if (!$current || $force) {
    // If not set, or if we're forcing the issue, then calculate and update.
    $timestamp = get_node_last_human_date($revision, $revision_id);
    if ($timestamp) {
      $date = format_time($timestamp);
      debug_log_message("Setting revision last-human date for revision $revision_id to $timestamp ($date).");
      set_revision_last_human_date($revision, $timestamp);
    }
    else {
      debug_log_message("Did not find a valid last-human date for revision $revision_id ; not updating.");
    }
  }
  else {
    $date = format_time($current);
    debug_log_message("Value already set for revision $revision_id to $current ($date); not overwriting.");
  }
}

/**
 * Set a node's human-last-touched date on latest and default revisions.
 *
 * @param int $nid
 *   An ID of a valid node object to alter.
 */
function set_node_last_human_date(int $nid): void {
  $latest = get_node_at_latest_revision($nid);
  $force = TRUE;
  if (!$latest->isDefaultRevision()) {
    // If the latest revision is not the default revision, then handle the
    // default revision separately.
    $default = get_node_at_default_revision($nid);
    $revision_id = $default->getRevisionId();
    debug_log_message("Inspecting default revision $revision_id for node $nid");
    update_revision($default, $force);
    $revision_id = $latest->getRevisionId();
    debug_log_message("Inspecting latest revision $revision_id for node $nid");
  }
  else {
    $revision_id = $latest->getRevisionId();
    debug_log_message("Inspecting latest & default revision $revision_id for node $nid");
  }
  update_revision($latest, $force);
}

/**
 * Get the queue!
 *
 * @return \Drupal\Core\Queue\QueueInterface
 *   The queue we work on.
 */
function get_queue(): QueueInterface {
  return \Drupal::service('queue')->get(CMS_QUEUE_NAME);
}

/**
 * Delete the queue.
 */
function delete_queue(): void {
  get_queue()->deleteQueue();
  debug_log_message("Deleted queue.");
}

/**
 * Load the queue.
 */
function load_queue(): void {
  $queue = get_queue();
  $nids = \Drupal::entityQuery('node')
    ->condition('status', '1')
    ->execute();
  $nid_count = count($nids);
  debug_log_message("Found $nid_count nodes...");
  $chunk_length = CMS_CHUNK_LENGTH;
  $chunks = array_chunk($nids, CMS_CHUNK_LENGTH);
  $chunk_count = count($chunks);
  debug_log_message("Processing $chunk_count chunks of $chunk_length nodes.");
  foreach ($chunks as $chunk_id => $chunk) {
    $item = [
      'id' => $chunk_id,
      'chunk' => $chunk,
    ];
    $queue->createItem($item);
  }
}

/**
 * Run the queue.
 */
function run_queue(): bool {
  $queue = get_queue();
  $item = $queue->claimItem();
  if (!$item) {
    // That's all, folks!
    return FALSE;
  }
  $chunk_id = $item->data['id'];
  $chunk = $item->data['chunk'];
  $chunk_length = count($chunk);
  debug_log_message("Processing chunk #$chunk_id ($chunk_length items)...");
  foreach ($chunk as $nid) {
    set_node_last_human_date($nid);
  }
  $queue->deleteItem($item);
  return TRUE;
}
