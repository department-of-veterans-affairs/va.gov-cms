<?php


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
