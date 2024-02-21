<?php

/**
 * @file
 * Common code related to drupal content scripts.
 *
 * This file can also be included in other things that run during non-full
 * bootstrap processes like hook_update_n, post update, and deploy.
 * Put the following line wherever you want to use this library.
 * require_once __DIR__ . '/script-library.php';
 */

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\UpdateException;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\UserStorageInterface;
use Psr\Log\LogLevel;

const CMS_MIGRATOR_ID = 1317;

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
 * Get an array of node ids for batch processing.
 *
 * @param string $node_bundle
 *   The bundle name of the nodes to lookup.
 * @param bool $published_only
 *   TRUE if you need only published nodes.
 *
 * @return array
 *   An array of nids for for the requested bundle.
 */
function get_nids_of_type($node_bundle, $published_only = FALSE): array {
  $query = \Drupal::entityQuery('node')
    ->condition('type', $node_bundle)
    ->accessCheck(FALSE);
  if ($published_only) {
    $query->condition('status', 1);
  }

  $nids = $query->execute();
  // Having a node ids as a numeric keyed array is problematic when it comes
  // to removing things from the array. As soon as you unset one, the array
  // becomes renumbered.  So we create string keys, with numeric values.
  // [35, 75, 20] becomes
  // ['node_35' => 35, 'node_75' => 75, 'node_20' => 20].
  $node_ids = array_combine(
    array_map('_va_gov_stringifynid', array_values($nids)),
    array_values($nids));
  return $node_ids;
}

/**
 * Saves a node revision with log messaging.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The node to serialize.
 * @param string $message
 *   The log message for the new revision.
 * @param bool $new
 *   Whether the revision should be created or updated.
 *
 * @return int
 *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
 */
function save_node_revision(NodeInterface $node, $message = '', $new = TRUE): int {
  $moderation_state = $node->get('moderation_state')->value;
  $node->setNewRevision($new);
  $node->setSyncing(TRUE);
  $node->setValidationRequired(FALSE);
  $node->enforceIsNew(FALSE);
  // New revisions deserve special treatment.
  if ($new) {
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $uid = CMS_MIGRATOR_ID;
  }
  else {
    $uid = $node->getRevisionUserId();
    // Append new log message to previous log message.
    $prefix = !empty($message) ? $node->getRevisionLogMessage() . ' - ' : '';
    $message = $prefix . $message;
  }
  $node->setRevisionUserId($uid);
  $revision_time = $node->getRevisionCreationTime();
  // Incrementing by a nano second to bypass Drupal core logic
  // that will update the "changed" value to request time if
  // the value is not different from the original value.
  $revision_time++;
  $node->setRevisionCreationTime($revision_time);
  $node->setRevisionLogMessage($message);
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
      ->accessCheck(FALSE)
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
 * Initializes the basic sandbox values.
 *
 * @param array $sandbox
 *   Standard drupal $sandbox var to keep state in hook_update_N.
 * @param string $counter_callback
 *   A function name to call to get the items to process. Must return an array.
 * @param array $callback_args
 *   A flat array of arguments to pass to the counter_callback.
 *
 * @throws Drupal\Core\Utility\UpdateException
 *   If the counter callback can not be found.
 */
function script_library_sandbox_init(array &$sandbox, $counter_callback, array $callback_args = []) {
  if (empty($sandbox['total'])) {
    // Sandbox has not been initiated.
    if (is_callable($counter_callback)) {
      $sandbox['items_to_process'] = call_user_func_array($counter_callback, $callback_args);
      $sandbox['total'] = count($sandbox['items_to_process']);
      $sandbox['current'] = 0;
      $sandbox['multi_run_state_key'] = "script_library_$counter_callback";

      // This seems like the first run, see if there is already a state saved
      // from a previous attempt.
      $last_run_completed = \Drupal::state()->get($sandbox['multi_run_state_key']);
      if (!is_null($last_run_completed)) {
        // A state exists, so alter the 'current' and 'items_to_process'.
        $sandbox['current'] = $last_run_completed + 1;
        // Remove the last successful run, and all that came before it.
        $sandbox['items_to_process'] = array_slice($sandbox['items_to_process'], $last_run_completed);
      }
    }
    else {
      // Something went wrong could not use callback. Throw exception.
      throw new UpdateException(
        "Counter callback {$counter_callback} provided in script_library_sandbox_init() is not callable. Can not proceed."
      );
    }
  }
  $sandbox['element'] = array_key_first($sandbox['items_to_process']);
}

/**
 * Updates the counts and log if complete.
 *
 * @param array $sandbox
 *   Hook_update_n sandbox for keeping state.
 * @param string $completed_message
 *   Message to log when completed. Can use '@completed' and '@total' as tokens.
 *
 * @return string
 *   String to be used as update hook messages.
 */
function script_library_sandbox_complete(array &$sandbox, $completed_message) {
  // Determine when to stop batching.
  $sandbox['current'] = ($sandbox['total'] - count($sandbox['items_to_process']));
  // Save the 'current' value to state, to record a successful run.
  \Drupal::state()->set($sandbox['multi_run_state_key'], $sandbox['current']);
  $sandbox['#finished'] = (empty($sandbox['total'])) ? 1 : ($sandbox['current'] / $sandbox['total']);
  $vars = [
    '@completed' => $sandbox['current'],
    '@element' => $sandbox['element'],
    '@total' => $sandbox['total'],
  ];

  $message = t('Processed @element. @completed/@total.', $vars) . PHP_EOL;
  // Log the all finished notice.
  if ($sandbox['#finished'] === 1) {
    Drupal::logger('script_library')->log(LogLevel::INFO, $completed_message, $vars);
    $logged_message = new FormattableMarkup($completed_message, $vars);
    $message = t('Process completed:') . " {$logged_message}" . PHP_EOL;
    // Delete the state as it is no longer needed.
    \Drupal::state()->delete($sandbox['multi_run_state_key']);
  }
  return $message;
}

/**
 * Lookup a key in a map array and return the value from the map.
 *
 * @param string|null $lookup
 *   A map key to lookup. Do not lookup int as indexes can shift.
 * @param array $map
 *   An array containing string key value pairs. [lookup => value].
 * @param bool $strict
 *   TRUE = only want a value from the array, FALSE = want your lookup back.
 *
 * @return mixed
 *   Whatever the value associated with the key.
 */
function script_libary_map_to_value(string|null $lookup, array $map, bool $strict = TRUE) : mixed {
  if (empty($lookup)) {
    if (isset($map['default'])) {
      // There is a default set, so use it.
      return $map['default'];
    }
    elseif ($strict) {
      return NULL;
    }
    else {
      return $lookup;
    }
  }
  if ($strict) {
    // Strict, so either it is there, or nothing.
    return $map[$lookup] ?? NULL;
  }
  else {
    // Not strict, so pass back what given it its not in the map.
    return $map[$lookup] ?? $lookup;
  }
}

/**
 * Turns on or off queueing of items to the post_api.
 *
 * CAUTION: The only time we would want to fully disable queueing is during a
 * deploy when editors can not save anything.
 *
 * @param bool $state
 *   TRUE to toggle the settings on, FALSE to toggle them off.
 */
function script_library_disable_post_api_queueing(bool $state): void {
  $on = ($state) ? 1 : 0;
  $config_post_api = \Drupal::configFactory()->getEditable('post_api.settings');
  $config_post_api->set('disable_queueing', $on)
    ->save(FALSE);
  script_library_skip_post_api_data_check($state);
}

/**
 * Turns on or off data checks and deduping for adding items to post_api queue.
 *
 * @param bool $state
 *   TRUE to toggle the settings on, FALSE to toggle them off.
 */
function script_library_skip_post_api_data_check(bool $state): void {
  $on = ($state) ? 1 : 0;
  $config_va_gov_post_api = \Drupal::configFactory()->getEditable('va_gov_post_api.settings');
  $config_va_gov_post_api->set('bypass_data_check', $on)
    ->save(FALSE);
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
