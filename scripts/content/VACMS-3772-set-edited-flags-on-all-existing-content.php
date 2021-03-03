<?php

/**
 * @file
 * One-time script to set edited flags on all content for each editing user.
 */

/**
 * Get a list of distinct nid, revision_uid tuples.
 *
 * This would be a cumbersome operation through the API:
 *   * load all nodes and loop through each node
 *   * get all revision IDs for the node and loop through each revision
 *   * load the node at that revision ID
 *   * get the revision user and if not already in the list, add to the list.
 *
 * A direct database query is far simpler.
 *
 * @return array[]
 *   A list of nid, uid tuples.
 */
function get_nid_uid_pairs(): array {
  $database = \Drupal::database();
  $query = $database->select('node_revision', 'r');
  $query->addField('r', 'nid', 'nid');
  $query->addField('r', 'revision_uid', 'uid');
  $query->condition('revision_uid', '0', '<>');
  $statement = $query
    ->distinct()
    ->execute();
  $result = [];
  foreach ($statement as $item) {
    $result[] = [
      'nid' => (int) $item->nid,
      'uid' => (int) $item->uid,
    ];
  }
  echo count($result) . " results found.\n";
  return $result;
}

/**
 * Do the thing.
 */
function run(): void {
  $flag_service = \Drupal::service('flag');
  $flagging_service = \Drupal::service('va_gov_notifications.flagging');
  $entity_type_manager = \Drupal::entityTypeManager();
  $user_storage = $entity_type_manager->getStorage('user');
  $node_storage = $entity_type_manager->getStorage('node');
  $pairs = get_nid_uid_pairs();
  foreach ($pairs as $pair) {
    $uid = $pair['uid'];
    $nid = $pair['nid'];
    $node = $node_storage->load($nid);
    $user = $user_storage->load($uid);
    echo "Flagging node {$node->id()} as edited by user {$user->getDisplayName()} ({$user->id()}).\n";
    $flagging_service->setEditedFlag($node, $user);
  }
}

run();
