<?php

/**
 * @file
 * Common code related to VACMS #5744 useful across multiple scripts.
 */

use Drupal\Core\Language\LanguageInterface;
use Drupal\entity_clone\EntityClone\Content\ContentEntityCloneBase;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

// Notices might mean my world is getting rocked.
error_reporting(E_ALL);

// Site alert label.
const SITE_ALERT_LABEL = 'VACMS-5744: Clone and Repair Paragraphs';

// Site alert message.
const SITE_ALERT_MESSAGE = <<<EOF
  🚧 We are cleaning up some old data; while this alert is in place, some content may be locked and not editable. 🚧
EOF;

// User ID used to make these changes.
const USER_ID = 1317;

// Exception codes.
const EXCEPTION_COULD_NOT_LOCK = 1;
const EXCEPTION_UNEXPECTED_DIFFERENCES = 2;

// A weird node I don't want to deal with.
const ELDRITCH_NODE_ID = 23;

/**
 * Log a message.
 *
 * @param string $message
 *   The message to log.
 */
function log_message(string $message): void {
  // \Drupal::logger(__FILE__)->notice($message);
  echo $message . PHP_EOL . PHP_EOL;
}

/**
 * Create a site alert.
 */
function create_site_alert(): void {
  $options = [];
  $options['active'] = TRUE;
  \Drupal::service('site_alert.cli_commands')
    ->create(SITE_ALERT_LABEL, SITE_ALERT_MESSAGE, $options);
}

/**
 * Delete the site alert.
 */
function delete_site_alert(): void {
  \Drupal::service('site_alert.cli_commands')
    ->delete(SITE_ALERT_LABEL);
}

/**
 * Switch to the designated user.
 */
function switch_user() {
  \Drupal::service('account_switcher')
    ->switchTo(User::load(USER_ID));
}

/**
 * Lock the specified node.
 *
 * @param int $nid
 *   The node ID.
 */
function lock_node(int $nid): void {
  $lock_service = \Drupal::service('content_lock');
  if (!$lock_service->locking($nid, LanguageInterface::LANGCODE_NOT_SPECIFIED, '*', USER_ID)) {
    throw new \Exception("Could not lock node $nid.");
  }
}

/**
 * Check whether the specified node is locked.
 *
 * @param int $nid
 *   The node ID.
 *
 * @return bool
 *   TRUE if the node is locked, otherwise FALSE.
 */
function is_locked_node(int $nid): bool {
  $lock_service = \Drupal::service('content_lock');
  return $lock_service->isLockedBy($nid, LanguageInterface::LANGCODE_NOT_SPECIFIED, '*', USER_ID);
}

/**
 * Unlock a specified node.
 *
 * @param int $nid
 *   The node ID.
 */
function unlock_node(int $nid): void {
  $lock_service = \Drupal::service('content_lock');
  $lock_service->release($nid, LanguageInterface::LANGCODE_NOT_SPECIFIED, '*', USER_ID);
  if (is_locked_node($nid)) {
    throw new \Exception("Could not unlock node {$nid}.");
  }
}

/**
 * Compare two associative arrays.
 *
 * @param array $array1
 *   The first array.
 * @param array $array2
 *   The second array.
 *
 * @return array
 *   An array composed of the differences between the two arrays.
 */
function compare_arrays(array $array1, array $array2): array {
  $result = [];

  foreach ($array1 as $key => $value1) {
    if (array_key_exists($key, $array2)) {
      $value2 = $array2[$key];
      if (is_array($value1) && is_array($value2)) {
        $diff = compare_arrays($value1, $value2);
        if (count($diff)) {
          $result[$key] = [
            'changes' => $diff,
          ];
        }
      }
      elseif ($value2 !== $value1) {
        $result[$key] = [
          'old' => $value1,
          'new' => $value2,
        ];
      }
      unset($array2[$key]);
    }
    else {
      $result[$key] = [
        'old' => $value1,
      ];
    }
  }
  foreach ($array2 as $key => $value) {
    $result[$key] = [
      'new' => $value,
    ];
  }
  return $result;
}

/**
 * Filter irrelevant information from an associative array.
 *
 * @param array $array
 *   An array to filter.
 *
 * @return array
 *   A filter, minus some keys we could do without.
 */
function filter_array_junk(array $array): array {
  $ignore_keys = [
    'changed',
    'id',
    'revision_log',
    'revision_timestamp',
    'revision_translation_affected',
    'revision_uid',
    'target_revision_id',
    'uuid',
    'vid',
  ];
  $result = [];
  foreach ($array as $key => $value) {
    if (in_array($key, $ignore_keys)) {
      continue;
    }
    if (is_array($value)) {
      $filtered_array = filter_array_junk($value);
      if (!empty($filtered_array)) {
        $result[$key] = $filtered_array;
      }
    }
    elseif (!empty($value)) {
      $result[$key] = $value;
    }
  }
  return $result;
}

/**
 * Compare relevant info from two associative arrays built from nodes.
 *
 * @param array $array1
 *   The first array.
 * @param array $array2
 *   The second array.
 *
 * @return array
 *   An array composed of the differences between the two arrays.
 */
function compare_filtered_arrays(array $array1, array $array2): array {
  return compare_arrays(filter_array_junk($array1), filter_array_junk($array2));
}

/**
 * Retrieve a list of all paragraph item fields on all entities.
 *
 * We can do this instead of retrieving the field config because we're only
 * worried about fields with existing data.
 *
 * @param string $parent_type
 *   The parent entity type.
 *
 * @return string[]
 *   A list of field names with existing paragraph items.
 */
function get_paragraph_field_names(string $parent_type = 'node'): array {
  $database = \Drupal::database();

  $query = $database->select('paragraphs_item_field_data', 'p');
  $query->condition('parent_type', $parent_type);
  $query->addField('p', 'parent_field_name', 'parent_field_name');
  $result = $query->distinct()->execute()->fetchCol();

  return $result;
}

/**
 * Retrieve a list of paragraph IDs and the parents to which they point.
 *
 * This queries for distinct (pid, nid) tuples, and then filters this
 * paragraphs with revisions referencing multiple parent nodes.
 *
 * @param string $parent_type
 *   The parent type, if desired.
 *
 * @return array[]
 *   An associative array with the form $pid => [ $nid1, $nid2, ...].
 */
function get_multiply_parented_paragraph_hash(string $parent_type = NULL): array {
  $database = \Drupal::database();

  $query = $database->select("paragraphs_item_revision_field_data", 'paragraphs_item_revision');
  $query->join('node_revision', 'node', "node.nid=paragraphs_item_revision.parent_id");
  $query->addField('paragraphs_item_revision', 'id', 'paragraph_id');
  $query->addField('paragraphs_item_revision', 'parent_id');
  $query->condition('parent_id', ELDRITCH_NODE_ID, "<>");
  $query->condition('parent_id', "0", "<>");
  if (isset($parent_type)) {
    $query->condition('parent_type', $parent_type);
  }
  $statement = $query->distinct()->execute();

  $hash = [];
  foreach ($statement as $item) {
    $pid = (int) $item->paragraph_id;
    $nid = (int) $item->parent_id;
    $hash[$pid] = $hash[$pid] ?? [];
    $hash[$pid][] = $nid;
  }

  $result = array_filter($hash, function ($nid_list) {
    return count($nid_list) > 1;
  });

  ksort($result);

  return $result;
}

/**
 * Retrieve a list of all parents of multiply-parented paragraphs.
 *
 * This is an inversion of get_multiply_parented_paragraph_hash().
 *
 * @return array[]
 *   An associative array with the form $nid => [ $pid1, $pid2, ...].
 */
function get_multiply_parented_paragraph_parent_hash(string $parent_type = NULL): array {
  $pid_hash = get_multiply_parented_paragraph_hash($parent_type);

  $result = [];
  foreach ($pid_hash as $pid => $nids) {
    foreach ($nids as $nid) {
      $nid = (int) $nid;
      $result[$nid] = $result[$nid] ?? [];
      $result[$nid][] = (int) $pid;
    }
  }

  ksort($result);

  return $result;
}

/**
 * Retrieve a list of all parent IDs for a paragraph.
 *
 * This runs a query to retrieve _all_ parents for a given paragraph, not just
 * those that are referred to directly by a paragraph's default revision.
 *
 * @param int $pid
 *   The paragraph ID.
 *
 * @return int[]
 *   The IDs of the nodes referred to by this paragraph.
 */
function get_all_parent_ids_for_paragraph(int $pid): array {
  $database = \Drupal::database();

  $query = $database->select('paragraphs_item_revision_field_data', 'paragraphs_item_revision');
  $query->addField('paragraphs_item_revision', 'parent_id');
  $query->condition('id', $pid);
  $query->condition('parent_id', ELDRITCH_NODE_ID, "<>");
  $query->condition('parent_id', "0", "<>");

  $result = array_map('intval', $query->distinct()->execute()->fetchCol());
  return $result;
}

/**
 * Retrieve a list of all paragraph IDs for a given parent.
 *
 * This runs a query to retrieve _all_ paragraphs pointing to a given parent,
 * not just those pointing from a default revision.
 *
 * @param int $parent_id
 *   The parent entity ID.
 *
 * @return int[]
 *   The IDs of the paragraphs referring to this parent.
 */
function get_all_paragraph_ids_for_parent(int $parent_id): array {
  $database = \Drupal::database();

  $query = $database->select('paragraphs_item_revision_field_data', 'paragraphs_item_revision');
  $query->addField('paragraphs_item_revision', 'id');
  $query->condition('parent_id', $parent_id);

  $result = array_map('intval', $query->distinct()->execute()->fetchCol());
  return $result;
}

/**
 * Retrieve a list of mis-parented node <-> paragraph rows.
 *
 * This runs a query for a given field name, listing all nodes that refer to a
 * paragraph whose default revision parent_id does not point back to that node.
 *
 * @param string $parent_type
 *   The machine name of the parent type, e.g. 'node'.
 * @param string $field_name
 *   The field name on the content type pointing to a paragraph.
 *
 * @return object[]
 *   A list of stdClass objects:
 *     - parent_deleted (bool): whether this node has been deleted.
 *     - parent_id (int): an entity ID.
 *     - parent_delta (int): the delta of this item in the field.
 *     - parent_revision_id (int): the revision of the parent.
 *     - parent_field_name (string): a field machine name.
 *     - target_id (int): a paragraph entity ID.
 *     - target_revision_id (int): a paragraph entity revision ID.
 *     - target_revision_timestamp (int): the revision created timestamp.
 *     - target_revision_date (string): The revision time in ATOM format.
 */
function get_misparented_paragraph_rows(string $parent_type, string $field_name): array {
  $database = \Drupal::database();

  $query = $database->select("{$parent_type}__{$field_name}", 'parent');
  $query->join('paragraphs_item_field_data', 'paragraph', "parent.{$field_name}_target_id = paragraph.id AND parent.entity_id <> paragraph.parent_id");
  $query->addField('parent', 'entity_id', 'parent_id');
  $query->addField('parent', 'deleted', 'parent_deleted');
  $query->addField('parent', 'delta', 'parent_delta');
  $query->addField('parent', 'revision_id', 'parent_revision_id');
  $query->addField('paragraph', 'created', 'target_created');
  $query->addField('paragraph', 'id', 'target_id');
  $query->addField('paragraph', 'revision_id', 'target_revision_id');
  $query->condition('parent.entity_id', "0", "<>");
  $query->condition('parent.entity_id', ELDRITCH_NODE_ID, "<>");
  $query->condition('paragraph.id', "0", "<>");
  $query->condition('paragraph.parent_type', $parent_type);
  $query->condition('paragraph.parent_field_name', $field_name);
  $statement = $query->execute();

  $result = [];
  foreach ($statement as $item) {
    $result[] = (object) [
      'parent_deleted' => (bool) $item->parent_deleted,
      'parent_id' => (int) $item->parent_id,
      'parent_delta' => (int) $item->parent_delta,
      'parent_revision_id' => (int) $item->parent_revision_id,
      'parent_field_name' => $field_name,
      'target_id' => (int) $item->target_id,
      'target_revision_id' => (int) $item->target_revision_id,
      'target_revision_timestamp' => (int) $item->target_created,
      'target_revision_date' => date(DATE_ATOM, $item->target_created),
    ];
  }

  return $result;
}

/**
 * Return IDs of the paragraphs that need to be re-cloned.
 *
 * @param int $nid
 *   The ID of the node that is parenting some improperly-cloned paragraphs.
 * @param int $revision_id
 *   The ID of the node revision we're checking.
 * @param string $field_name
 *   The name of the field we're checking.
 *
 * @return int[]
 *   A list of paragraph IDs.
 */
function get_currently_improperly_cloned_paragraph_ids(int $nid, int $revision_id, string $field_name): array {
  log_message("Getting improperly-cloned paragraph IDs for node $nid at revision $revision_id on field $field_name...");

  $database = \Drupal::database();

  $query = $database->select("node_revision__{$field_name}", 'parent');
  $query->join('paragraphs_item_revision_field_data', 'paragraph', "parent.{$field_name}_target_id=paragraph.id AND parent.{$field_name}_target_revision_id=paragraph.revision_id AND paragraph.parent_type='node'");
  $query->join("node_revision__{$field_name}", 'parent2', "parent2.{$field_name}_target_id=paragraph.id AND parent2.entity_id<>parent.entity_id");
  $query->join("node_revision", 'node', "node.nid=parent.entity_id");
  $query->addField('paragraph', 'id', 'paragraph_id');
  $query->condition('parent.entity_id', $nid);
  $query->condition('parent.revision_id', $revision_id);

  $result = $query->distinct()->execute()->fetchCol();

  $result = array_map('intval', $result);

  sort($result);

  return $result;
}

/**
 * Return a list of node IDs with which the specified node shares paragraphs.
 *
 * @param int $nid
 *   The node ID.
 * @param int $revision_id
 *   The revision ID.
 * @param string $field_name
 *   The node paragraph field.
 *
 * @return int[]
 *   A list of node IDs with which this node revision shares paragraphs.
 */
function get_currently_coparenting_node_ids(int $nid, int $revision_id, string $field_name): array {
  $database = \Drupal::database();

  $query = $database->select("node_revision__{$field_name}", 'parent');
  $query->join('paragraphs_item_revision_field_data', 'paragraph', "parent.{$field_name}_target_id=paragraph.id AND parent.{$field_name}_target_revision_id=paragraph.revision_id AND paragraph.parent_type='node'");
  $query->join("node_revision__{$field_name}", 'parent2', "parent2.{$field_name}_target_id=paragraph.id AND parent2.entity_id<>parent.entity_id");
  $query->join("node_revision", 'node', "node.nid=parent.entity_id");
  $query->addField('parent2', 'entity_id');
  $query->condition('parent.entity_id', $nid);
  $query->condition('parent.revision_id', $revision_id);
  $query->condition('paragraph.id', "0", "<>");
  $query->condition('paragraph.parent_type', 'node');
  $query->condition('paragraph.parent_field_name', $field_name);

  $result = $query->distinct()->execute()->fetchCol();

  return $result;
}

/**
 * Confirm that a node revision is CURRENTLY improperly cloned.
 *
 * @param int $nid
 *   The node ID.
 * @param int $revision_id
 *   The node revision ID.
 * @param string $field_name
 *   The node paragraph field.
 *
 * @return bool
 *   TRUE if this revision is incompletely cloned, otherwise FALSE.
 */
function is_currently_incompletely_cloned_node_revision(int $nid, int $revision_id, string $field_name): bool {
  $coparenting_node_ids = get_currently_coparenting_node_ids($nid, $revision_id, $field_name);
  return count($coparenting_node_ids) > 0;
}

/**
 * Returns a serialized form of the node as JSON.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The node to serialize.
 *
 * @return string
 *   The JSON-serialized form of this node.
 */
function get_node_serialization(NodeInterface $node): string {
  $serializer = \Drupal::service('serializer');
  $result = $serializer->serialize($node, 'json');
  $paragraph_field_names = get_paragraph_field_names();
  $paragraph_field_names = array_filter($paragraph_field_names, function ($field_name) use ($node) {
    return $node->hasField($field_name);
  });
  $object = json_decode($result, TRUE);
  foreach ($paragraph_field_names as $field_name) {
    $field_value = $node->get($field_name);
    $paragraphs = $field_value->referencedEntities();
    $object[$field_name] = array_map(function ($paragraph) use ($serializer) {
      return json_decode($serializer->serialize($paragraph, 'json'), TRUE);
    }, $paragraphs);
  }
  $result = json_encode($object, JSON_PRETTY_PRINT);
  return $result;
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
  $entity_type_manager = \Drupal::entityTypeManager();
  $node_storage = $entity_type_manager->getStorage('node');
  $result = $node_storage->loadRevision($node_storage->getLatestRevisionId($nid));
  return $result;
}

/**
 * Confirm that a node is CURRENTLY improperly cloned.
 *
 * We can tell if a node is CURRENTLY improperly cloned if:
 *   1) its default revision, or
 *   2) its latest revision, if not the same as 1),
 * are improperly cloned.
 *
 * @param int $nid
 *   The Node ID.
 *
 * @return bool
 *   TRUE if the node needs to be repaired, otherwise FALSE.
 */
function is_currently_incompletely_cloned_node(int $nid): bool {
  $entity_type_manager = \Drupal::entityTypeManager();
  $node_storage = $entity_type_manager->getStorage('node');
  $latest_revision_id = $node_storage->getLatestRevisionId($nid);
  $paragraph_field_names = get_paragraph_field_names();
  foreach ($paragraph_field_names as $field_name) {
    if (is_currently_incompletely_cloned_node_revision($nid, $latest_revision_id, $field_name)) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Get a list of CURRENTLY improperly cloned nodes.
 *
 * @return int[]
 *   A list of nodes whose default and/or latest revisions are problematic.
 */
function get_currently_improperly_cloned_nodes(): array {
  $paragraph_parent_hash = get_multiply_parented_paragraph_parent_hash('node');
  $nids = array_keys($paragraph_parent_hash);
  $result = [];
  foreach ($nids as $nid) {
    try {
      $is_problematic = is_currently_incompletely_cloned_node($nid);
    }
    catch (\Exception $exception) {
      log_message($exception->getMessage());
      continue;
    }
    if ($is_problematic) {
      $result[] = $nid;
    }
  }
  sort($result);
  return $result;
}

/**
 * Repair a node that was subject to a botched cloning operation.
 *
 * @param \Drupal\node\NodeInterface $node
 *   The node to repair.
 */
function repair_node(NodeInterface $node): void {
  $entity_type_manager = \Drupal::entityTypeManager();
  $time_service = \Drupal::service('datetime.time');
  $current_user = \Drupal::service('current_user');

  $cloner = new ContentEntitycloneBase($entity_type_manager, 'node', $time_service, $current_user);

  $paragraph_field_names = get_paragraph_field_names();
  $paragraph_field_names = array_filter($paragraph_field_names, function ($field_name) use ($node) {
    return $node->hasField($field_name);
  });

  $already_cloned = [];
  $properties = [
    'children' => [],
  ];
  $revision_message = "Attempted repair of node (see VACMS-5744):\n";
  $modifications = [];
  $modified = FALSE;

  foreach ($paragraph_field_names as $field_name) {
    $nid = $node->id();
    $revision_id = $node->getRevisionId();
    $pids = get_currently_improperly_cloned_paragraph_ids($nid, $revision_id, $field_name);
    if (empty($pids)) {
      continue;
    }
    log_message('Retrieved ' . count($pids) . " current improperly cloned paragraph IDs for nid $nid at revision $revision_id on field $field_name : " . json_encode($pids));
    $field_value = $node->get($field_name);
    $field_items = $field_value->getValue();
    $referenced_entities = $field_value->referencedEntities();
    foreach ($field_items as $key => $field_item) {
      $pid = (int) $field_item['target_id'];
      $nid = $node->id();
      if (in_array($pid, $pids)) {
        log_message("Found problem paragraph $pid on node $nid at revision $revision_id on field $field_name... replacing...");
        $paragraph = $referenced_entities[$key];
        $dupe = $paragraph->createDuplicate();
        $clone = $cloner->cloneEntity($paragraph, $dupe, $properties, $already_cloned);
        $clone->setParentEntity($node, $field_name);
        $node->get($field_name)->set($key, $clone);
        $modifications[] = "Cloned paragraph $pid at $field_name [$key] to {$clone->id()}.";
        $modified = TRUE;
      }
    }
  }
  if (!$modified) {
    log_message("Node $nid was not marked as modified; not attempting to save...");
    return;
  }
  $node->setNewRevision();
  $node->setRevisionLogMessage($revision_message . '<ul><li>' . implode('</li><li>', $modifications) . '</li></ul>');
  $node->setRevisionCreationTime(time());
  $node->setRevisionUserId(USER_ID);
  log_message("Attempting to save following modifications to node $nid: " . json_encode($modifications, JSON_PRETTY_PRINT));
  $node->save();
  log_message("Node saved successfully.");
}

/**
 * Process a node that was subject to a botched cloning operation.
 *
 * The procedure would seem to be:
 *
 * 1. Verify that the node is currently problematic.
 * 2. Lock the node.
 * 3. Load the latest revision of this node.
 * 4. Clone all problematic paragraphs attached to the node.
 * 5. Save a new node revision, replacing problematic paragraphs.
 * 6. Verify the node has been repaired.
 * 7. Unlock the node.
 *
 * @param int $nid
 *   The node ID.
 */
function process_node(int $nid): void {
  if (!is_currently_incompletely_cloned_node($nid)) {
    log_message("Skipping requested repair of node $nid because it does not appear to be improperly cloned.");
    return;
  }
  try {
    log_message("Locking node $nid...");
    lock_node($nid);
  }
  catch (\Exception $exception) {
    log_message($exception->getMessage());
    throw new \Exception("Unable to repair node $nid... the node could not be locked.", EXCEPTION_COULD_NOT_LOCK);
  }
  $node = get_node_at_latest_revision($nid);
  $before_serialized = json_decode(get_node_serialization($node), TRUE);
  repair_node($node);
  $node = get_node_at_latest_revision($nid);
  $after_serialized = json_decode(get_node_serialization($node), TRUE);
  $compared_revisions = compare_filtered_arrays($before_serialized, $after_serialized);
  if (count($compared_revisions)) {
    throw new \Exception("The node repair process for node $nid concluded with the following data differences: " . json_encode($compared_revisions, JSON_PRETTY_PRINT), EXCEPTION_UNEXPECTED_DIFFERENCES);
  }
  if (is_currently_incompletely_cloned_node($nid)) {
    throw new \Exception("The node repair process for node $nid has not completely repaired the node.  It remains in an incompletely cloned state.");
  }
  log_message("Unlocking node $nid...");
  unlock_node($nid);
}

/**
 * Runs a report on the current database contents.
 *
 * @return array
 *   An associative array with the following structure:
 *     - 'paragraph_field_names'
 */
function run_report(): array {
  $result = [
    'paragraph_field_names' => get_paragraph_field_names(),
    'multiply_parented_paragraphs' => get_multiply_parented_paragraph_hash('node'),
    'multiply_parented_paragraph_parents' => get_multiply_parented_paragraph_parent_hash('node'),
    'currently_improperly_cloned_nodes' => get_currently_improperly_cloned_nodes(),
  ];
  return $result;
}

/**
 * Prints a formatted report on the current database contents.
 */
function print_report(): void {
  $data = run_report();
  log_message('Discovered ' . count($data['paragraph_field_names']) . ' paragraph fields with existing data: ' . json_encode($data['paragraph_field_names']));
  log_message('Discovered ' . count($data['multiply_parented_paragraphs']) . ' multiply parented paragraphs: ' . json_encode($data['multiply_parented_paragraphs']));
  log_message('Discovered ' . count($data['multiply_parented_paragraph_parents']) . ' multiply parented paragraph parents: ' . json_encode($data['multiply_parented_paragraph_parents']));
  log_message('Discovered ' . count($data['currently_improperly_cloned_nodes']) . ' currently improperly cloned nodes: ' . json_encode($data['currently_improperly_cloned_nodes']));
}
