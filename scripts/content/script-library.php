<?php

/**
 * @file
 * Common code related to drupal content scripts.
 */

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\UserStorageInterface;

define('CMS_MIGRATOR_ID', 1317);

/**
 * Log a message to stdout.
 *
 * @param string $message
 *   The message to log.
 */
function debug_log_message(string $message): void {
  // \Drupal::logger(__FILE__)->notice($message);
  echo $message . PHP_EOL;
}

/**
 * Entity type manager.
 *
 * @return \Drupal\Core\Entity\EntityTypeManagerInterface
 *   The entity type manager service.
 */
function entity_type_manager(): EntityTypeManagerInterface {
  static $entity_type_manager;
  if (is_null($entity_type_manager)) {
    $entity_type_manager = \Drupal::entityTypeManager();
  }
  return $entity_type_manager;
}

/**
 * Get the node storage.
 *
 * @return \Drupal\node\NodeStorageInterface
 *   Node storage.
 */
function get_node_storage(): NodeStorageInterface {
  return entity_type_manager()->getStorage('node');
}

/**
 * Get the term storage.
 *
 * @return \Drupal\taxonomy\TermStorageInterface
 *   Term storage.
 */
function get_term_storage(): NodeStorageInterface {
  return entity_type_manager()->getStorage('taxonomy_term');
}

/**
 * Get the user storage.
 */
function get_user_storage(): UserStorageInterface {
  return entity_type_manager()->getStorage('user');
}

/**
 * Switch to the CMS Migrator user.
 *
 * @param int|null $uid
 *   The UID of the account to switch.
 */
function switch_user(?int $uid = NULL): void {
  $uid = $uid ?? CMS_MIGRATOR_ID;
  $user = get_user_storage()->load($uid);
  \Drupal::service('account_switcher')
    ->switchTo($user);
  debug_log_message("Acting as {$user->getDisplayName()} [{$uid}]");
}

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
  $node_storage = get_node_storage();
  return $node_storage->loadRevision($node_storage->getLatestRevisionId($nid));
}

/**
 * Load the default revision of a node.
 *
 * @param int $nid
 *   The node ID.
 *
 * @return \Drupal\node\NodeInterface
 *   The latest revision of that node.
 */
function get_node_at_default_revision(int $nid): NodeInterface {
  return get_node_storage()->load($nid);
}

/**
 * Load all revisions of a node.
 *
 * @param int $nid
 *   The node ID.
 *
 * @return \Drupal\node\NodeInterface[]
 *   All revisions of that node.
 */
function get_node_all_revisions(int $nid): array {
  $node_storage = get_node_storage();
  $node = $node_storage->load($nid);
  $vids = $node_storage->revisionIds($node);
  return $node_storage->loadMultipleRevisions($vids);
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
 *
 * @return int
 *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
 */
function save_node_revision(NodeInterface $node, $message): int {
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
  return $node->save();
}

/**
 * Saves a node revision with no new revision or log.
 *
 * @param \Drupal\node\NodeInterface $revision
 *   The node to serialize.
 *
 * @return int
 *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
 */
function save_node_existing_revision_without_log(NodeInterface $revision): int {
  $revision->setNewRevision(FALSE);
  $revision->enforceIsNew(FALSE);
  $revision->setSyncing(TRUE);
  $revision->setValidationRequired(FALSE);
  $revision_time = $revision->getRevisionCreationTime();
  // Incrementing by a nano second to bypass Drupal core logic
  // that will update the "changed" value to request time if
  // the value is not different from the original value.
  $revision_time++;
  $revision->setRevisionCreationTime($revision_time);
  $revision->setChangedTime($revision_time);
  return $revision->save();
}

/**
 * Create new terms for if they do not exist.
 *
 * @param string $vocabulary_id
 *   The machine name of the taxonomy vocabulary.
 * @param array $terms
 *   An array of terms in the form of 'term name' => 'description'.
 *
 * @return int
 *   The number of terms created.
 */
function save_new_terms($vocabulary_id, array $terms): int {
  $terms_created = 0;
  foreach ($terms as $name => $description) {
    // Make sure we are not creating duplicate terms.
    $tid = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $name)
      ->condition('vid', $vocabulary_id)
      ->execute();
    if (empty($tid)) {
      // Term does not exist, so create it.
      $term = Term::create([
        'name' => $name,
        'vid' => $vocabulary_id,
      ]);
      $term->setNewRevision(TRUE);
      $term->setDescription($description);
      $term->setRevisionUserId(CMS_MIGRATOR_ID);
      $term->setSyncing(TRUE);
      $term->setValidationRequired(FALSE);
      $term->save();
      $terms_created++;
    }
  }
  return $terms_created;
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
