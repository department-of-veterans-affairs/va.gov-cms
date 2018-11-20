<?php

/**
 * @file
 * Contains Behat Drush command file, for use by the Behat Drush Extension.
 */

include __DIR__ . '/behat-drush-common.inc';

/**
 * Implements hook_drush_init
 */
function behat_drush_init() {
  drush_autoload(__FILE__);
}

/**
 * Implements hook_drush_command().
 */
function behat_drush_command() {
  $items = array();

  $items['behat'] = array(
    'description' => "Behat Drush endpoint. Serves as an entrypoint for Behat to make remote calls into the Drupal site being tested.",
    // Describe the arguments for this command.  Delete
    // this seciton if command takes no arguments.
    'arguments' => array(
      'operation' => 'Behat operation, e.g. create-node.',
      'data' => 'Operation data in json format.',
    ),
    // Command options.
    'options' => array(),

    // Give one or more example commandline usages for this command.
    'examples' => array(
      "drush behat create-node '{\"title\":\"Example page\",\"type\":\"page\"}'" => 'Create a page with the title "Example page".',
    ),
    // Default output is json, --pipe produces yml.
    'outputformat' => array(
      'default' => 'json',
      'pipe-format' => 'yaml',
    ),
  );

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function behat_drush_help($section) {
  switch ($section) {
    case 'drush:behat':
      return dt("The Behat Drush endpoint command file must be installed in the target Drupal site in order to use more advanced features such as creating nodes and taxonomy terms.");
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_behat($operation, $json_data) {
  $data = json_decode($json_data);

  // Dispatch if the operation exists.
  $fn = 'drush_behat_op_' . strtr($operation, '-', '_');
  if (function_exists($fn)) {
    return $fn($data);
  }
  else {
    return drush_set_error('DRUSH_BEHAT_NO_OPERATION', dt("Operation '!op' unknown", array('!op' => $operation)));
  }
}

/**
 * Create a node.
 */
function drush_behat_op_create_node($node) {
  // Set original if not set.
  if (!isset($node->original)) {
    $node->original = clone $node;
  }

  // Assign authorship if none exists and `author` is passed.
  if (!isset($node->uid) && !empty($node->author) && ($user = user_load_by_name($node->author))) {
    $node->uid = $user->uid;
  }

  // Convert properties to expected structure.
  _drush_behat_expand_entity_properties($node);

  // Attempt to decipher any fields that may be specified.
  _drush_behat_expand_entity_fields('node', $node);

  // Set defaults that haven't already been set.
  $defaults = clone $node;
  node_object_prepare($defaults);
  $node = (object) array_merge((array) $defaults, (array) $node);

  node_save($node);
  return (array) $node;
}

/**
 * Delete a node.
 */
function drush_behat_op_delete_node($node) {
  node_delete($node->nid);
}

/**
 * Create a taxonomy term.
 */
function drush_behat_op_create_term($term) {
  // Map vocabulary names to vid, these take precedence over machine names.
  if (!isset($term->vid)) {
    $vocabularies = \taxonomy_get_vocabularies();
    foreach ($vocabularies as $vid => $vocabulary) {
      if ($vocabulary->name == $term->vocabulary_machine_name) {
        $term->vid = $vocabulary->vid;
      }
    }
  }

  if (!isset($term->vid)) {
    // Try to load vocabulary by machine name.
    $vocabularies = \taxonomy_vocabulary_load_multiple(FALSE, array(
      'machine_name' => $term->vocabulary_machine_name,
    ));
    if (!empty($vocabularies)) {
      $vids = array_keys($vocabularies);
      $term->vid = reset($vids);
    }
  }

  // If `parent` is set, look up a term in this vocab with that name.
  if (isset($term->parent)) {
    $parent = \taxonomy_get_term_by_name($term->parent, $term->vocabulary_machine_name);
    if (!empty($parent)) {
      $parent = reset($parent);
      $term->parent = $parent->tid;
    }
  }

  if (empty($term->vid)) {
    throw new \Exception(sprintf('No "%s" vocabulary found.'));
  }

  // Attempt to decipher any fields that may be specified.
  _drush_behat_expand_entity_fields('taxonomy_term', $term);

  \taxonomy_term_save($term);

  return $term;
}

/**
 * Delete a taxonomy term.
 */
function drush_behat_op_delete_term(\stdClass $term) {
  $status = 0;
  if (isset($term->tid)) {
    $status = \taxonomy_term_delete($term->tid);
  }
  return $status;
}

/**
 * Check if this is a field.
 */
function drush_behat_op_is_field($is_field_info) {
  list($entity_type, $field_name) = $is_field_info;
  $map = field_info_field_map();
  return !empty($map[$field_name]) && array_key_exists($entity_type, $map[$field_name]['bundles']);
}

/**
 * Expands properties on the given entity object to the expected structure.
 *
 * @param \stdClass $entity
 *   The entity object.
 *
 * @see Drupal\Driver\Cores\Drupal7\expandEntityProperties
 */
function _drush_behat_expand_entity_properties(\stdClass $entity) {
  // The created field may come in as a readable date, rather than a
  // timestamp.
  if (isset($entity->created) && !is_numeric($entity->created)) {
    $entity->created = strtotime($entity->created);
  }

  // Map human-readable node types to machine node types.
  $types = \node_type_get_types();
  foreach ($types as $type) {
    if ($entity->type == $type->name) {
      $entity->type = $type->type;
      continue;
    }
  }
}

/**
 * Get all of the field attached to the specified entity type.
 *
 * @see Drupal\Driver\Cores\Drupal7\getEntityFieldTypes in Behat
 */
function _drush_behat_get_entity_field_types($entity_type) {
  $return = array();
  $fields = field_info_field_map();
  foreach ($fields as $field_name => $field) {
    if (array_key_exists($entity_type, $field['bundles'])) {
      $return[$field_name] = $field['type'];
    }
  }
  return $return;
}

function _drush_behat_get_field_handler($entity, $entity_type, $field_name) {
  $core_namespace = "Drupal7";
  return _drush_behat_get_field_handler_common($entity, $entity_type, $field_name, $core_namespace);
}
