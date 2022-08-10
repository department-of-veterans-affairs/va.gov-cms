<?php

/**
 * @file
 * Common code related to drupal content scripts.
 */

use Drupal\node\NodeInterface;

const CMS_MIGRATOR_ID = 1317;

/**
 * Load the latest revision of a node.
 *
 * @param int $nid
 *   The node ID.
 *
 * @return \Drupal\node\NodeInterface
 *   The latest revision of that node.
 */
function get_node_at_latest_revision(int $nid): NodeInterface {
  $entity_type_manager = \Drupal::entityTypeManager();
  $node_storage = $entity_type_manager->getStorage('node');
  $result = $node_storage->loadRevision($node_storage->getLatestRevisionId($nid));
  return $result;
}

/**
 * Normalize all crisis hotline instances in a provided string.
 *
 * @param string $input
 *   The string to normalize.
 * @param bool $plain
 *   True if the result should be a plain string, false for html.
 *
 * @return string
 *   The value of $input with all crisis numbers updated.
 */
function normalize_crisis_number($input, $plain = FALSE): string {
  // @todo refactor/rename to search and replacement strings.
  $replacement_string = '988';
  $replacement_html = '<a aria-label="9 8 8" href="tel:988">988</a>';
  // Remove telephone "link" from number.
  $first_pattern = "/\<a[^>]*\>(?:1-)?800[\-\.]273[\-\.]8255\<\/a\>/i";
  $output = preg_replace($first_pattern, '800-273-8255', $input);
  // Remove area code prefixes.
  $output = str_replace('1-800-273-8255', '800-273-8255', $output);
  // All instances should now be 800-273-8255 and can be replaced.
  if ($plain) {
    $output = str_replace('800-273-8255', $replacement_string, $output);
  }
  else {
    $output = str_replace('800-273-8255', $replacement_html, $output);
  }
  return $output;
}

/**
 * Saves a node revision with log messaging.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The node to serialize.
 * @param string $message
 *   The log message for the new revision.
 */
function save_node_revision(NodeInterface $node, $message): void {
  $states = [
    'draft',
    'review',
  ];
  $moderation_state = $node->get('moderation_state')->value;
  $node->setNewRevision(TRUE);
  // If draft or review preserve the user from revision, otherwise CMS Migrator.
  $uid = (in_array($moderation_state, $states)) ? $node->getRevisionUserId() : CMS_MIGRATOR_ID;
  $node->setRevisionUserId($uid);
  $node->setChangedTime(time());
  $node->setRevisionCreationTime(time());
  // If draft or review append new log message to previous log message.
  $prefix = (in_array($moderation_state, $states)) ? $node->getRevisionLogMessage() . ' - ' : '';
  $node->setRevisionLogMessage($prefix . $message);
  $node->set('moderation_state', $moderation_state);
  $node->save();
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
