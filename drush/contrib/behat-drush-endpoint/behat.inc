<?php

/**
 * @file
 * Contains Behat Drush command file, for use by the Behat Drush Extension.
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

/**
 * Create a node.
 */
function drush_behat_op_create_node($node) {
  // Default status to 1 if not set.
  if (!isset($node->status)) {
    $node->status = 1;
  }
  // If 'author' is set, remap it to 'uid'.
  if (isset($node->author)) {
    $user = user_load_by_name($node->author);
    if ($user) {
      $node->uid = $user->id();
    }
  }

  // Attempt to decipher any fields that may be specified.
  _drush_behat_expand_entity_fields('node', $node);

  $entity = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->create((array) $node);

  $entity->save();

  $node->nid = $entity->id();

  return (array) $node;
}

/**
 * Delete a node.
 */
function drush_behat_op_delete_node($node) {
  $node = $node instanceof NodeInterface ? $node : Node::load($node->nid);
  if ($node instanceof NodeInterface) {
    $node->delete();
  }
}

/**
 * Create a taxonomy term.
 */
function drush_behat_op_create_term($term) {
  $term->vid = $term->vocabulary_machine_name;

  // Attempt to decipher any fields that may be specified.
  _drush_behat_expand_entity_fields('taxonomy_term', $term);

  $entity = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->create((array)$term);

  $entity->save();

  $term->tid = $entity->id();
  return (array) $term;
}

/**
 * Delete a taxonomy term.
 */
function drush_behat_op_delete_term(\stdClass $term) {
  $term = $term instanceof TermInterface ? $term : Term::load($term->tid);
  if ($term instanceof TermInterface) {
    $term->delete();
  }
}

/**
 * Check if this is a field.
 */
function drush_behat_op_is_field($is_field_info) {
  list($entity_type, $field_name) = $is_field_info;
  return _drush_behat_is_field($entity_type, $field_name);
}

/**
 * Get all of the field attached to the specified entity type.
 *
 * @see Drupal\Driver\Cores\Drupal8\getEntityFieldTypes in Behat
 */
function _drush_behat_get_entity_field_types($entity_type) {
  $return = array();
  $fields = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type);
  foreach ($fields as $field_name => $field) {
    if (_drush_behat_is_field($entity_type, $field_name)) {
      $return[$field_name] = $field->getType();
    }
  }
  return $return;
}

function _drush_behat_is_field($entity_type, $field_name) {
  $fields = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type);
  return (isset($fields[$field_name]) && $fields[$field_name] instanceof FieldStorageConfig);
}

function _drush_behat_get_field_handler($entity, $entity_type, $field_name) {
  $core_namespace = "Drupal8";
  return _drush_behat_get_field_handler_common($entity, $entity_type, $field_name, $core_namespace);
}

/**
 * Expands properties on the given entity object to the expected structure.
 *
 * @param \stdClass $entity
 *   Entity object.
 *
 * @see Drupal\Driver\Cores\AbstractCore\expandEntityFields
 */
function _drush_behat_expand_entity_fields($entity_type, \stdClass $entity) {
  $field_types = _drush_behat_get_entity_field_types($entity_type);
  foreach ($field_types as $field_name => $type) {
    if (isset($entity->$field_name)) {
      $entity->$field_name = _drush_behat_get_field_handler($entity, $entity_type, $field_name)
        ->expand($entity->$field_name);
    }
  }
}

/**
 * Get the field handler for the specified field of the specified entity.
 *
 * Note that this function instantiates a field handler class that is
 * provided by the Behat Drush Driver.  In order for this to work, an
 * appropriate autoload.inc file must be included.  This will be done
 * automatically if the Drupal site is managed by Composer, and requires
 * the Behat Drush Driver in its composer.json file.
 *
 * @see Drupal\Driver\Cores\AbstractCore\getFieldHandler
 */
function _drush_behat_get_field_handler_common($entity, $entity_type, $field_name, $core_namespace) {
  $field_types = _drush_behat_get_entity_field_types($entity_type);
  $camelized_type = _drush_behat_camelize($field_types[$field_name]);
  $default_class = sprintf('\Drupal\Driver\Fields\%s\DefaultHandler', $core_namespace);
  $class_name = sprintf('\Drupal\Driver\Fields\%s\%sHandler', $core_namespace, $camelized_type);
  if (class_exists($class_name)) {
    return new $class_name($entity, $entity_type, $field_name);
  }
  return new $default_class($entity, $entity_type, $field_name);
}

/**
 * Converts a seried of words into camel case.
 *
 * @see Symfony\Component\DependencyInjection\Container\camelize
 */
function _drush_behat_camelize($id) {
  return strtr(ucwords(strtr($id, array('_' => ' ', '.' => '_ ', '\\' => '_ '))), array(' ' => ''));
}
