<?php

/**
 * @file
 * Drush integration for taxonomy_entity_index.
 */

/**
 * Implements hook_drush_command().
 *
 * @See drush_parse_command() for a list of recognized keys.
 */
function taxonomy_entity_index_drush_command() {
  $items = [];

  $items['taxonomy-entity-index-rebuild'] = [
    'description' => 'Rebuild taxonomy entity index',
    'options' => [
      'entity_types' => 'A comma separated list of entity types to reindex.',
    ],
    'examples' => [
      'drush tei-rebuild' => 'Rebuilds the index for all entity types.',
      'drush tei-rebuild --entity_types="paragraph,content"' => 'Rebuilds the index for the paragraph and content entity types',
    ],
    'aliases' => ['tei-rebuild'],
  ];

  return $items;
}

/**
 * Rebuilds the taxonomy entity index.
 */
function drush_taxonomy_entity_index_rebuild() {
  $operations = [];
  $entity_types = drush_get_option('entity_types');

  if (empty($entity_types)) {
    $entity_types = array_keys(\Drupal::entityTypeManager()->getDefinitions());
  }
  else {
    $entity_types = explode(',', $entity_types);
  }

  foreach ($entity_types as $type) {
    $operations[] = ['taxonomy_entity_index_reindex_entity_type', [$type]];
  }

  $batch = [
    'operations' => $operations,
    'finished' => 'taxonomy_entity_index_finished',
  ];

  batch_set($batch);

  // Process the batch.
  $batch =& batch_get();
  $batch['progressive'] = FALSE;
  batch_process();
}

/**
 * Validates the taxonomy entity index rebuild command.
 */
function drush_taxonomy_entity_index_rebuild_validate() {
  if ($ids_submitted = drush_get_option('entity_types')) {
    $ids_submitted = explode(',', $ids_submitted);
    $ids_valid = array_keys(\Drupal::entityTypeManager()->getDefinitions());
    $ids_invalid = [];
    foreach ($ids_submitted as $id_submitted) {
      if (!in_array($id_submitted, $ids_valid)) {
        $ids_invalid[] = $id_submitted;
      }
    }
    if (!empty($ids_invalid)) {
      return drush_set_error('INVALID_ENTITY_TYPE', dt('The following are not valid entity types: ', ['@entity_type_id_submitted' => implode(',', $ids_invalid)]));
    }
  }
}
