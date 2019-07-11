<?php

/**
 * @file
 * Token integration for the taxonomy_entity_index module.
 */

use Drupal\Component\Utility\Unicode;

/**
 * Implements hook_token_info_alter().
 */
function taxonomy_entity_index_token_info_alter(&$info) {
  if (!\Drupal::moduleHandler()->moduleExists('token')) {
    // We depend on the token_get_entity_mapping() function in token.module.
    return $info;
  }

  // Add [entity:terms] tokens for all entities.
  $entities = \Drupal::entityManager()->getDefinitions();
  foreach ($entities as $entity_type => $entity_info) {
    if (!isset($entity_info['token type'])) {
      continue;
    }

    // Only add to valid token types.
    $token_type = $entity_info['token type'];
    if (!isset($info['tokens'][$token_type]) || !isset($info['types'][$token_type])) {
      continue;
    }

    // Do not add token if the entity type has no term fields.
    $fields = taxonomy_entity_index_get_taxonomy_field_names($entity_type);
    if (empty($fields)) {
      continue;
    }

    if (!isset($info['tokens'][$token_type]['terms'])) {
      $info['tokens'][$token_type]['terms'] = [
        'name' => t('Terms'),
        'description' => t('The terms associated with the @entity.', ['@entity' => Unicode::strtolower($entity_info['label'])]),
        'type' => 'array',
        'module' => 'taxonomy_entity_index',
      ];
    }
  }
}

/**
 * Implements hook_tokens().
 */
function taxonomy_entity_index_tokens($type, $tokens, array $data = [], array $options = []) {
  if (!\Drupal::moduleHandler()->moduleExists('token') || _token_module($type, 'terms') != 'taxonomy_entity_index') {
    // We depend on the token_get_entity_mapping() function in token.module.
    return [];
  }

  $replacements = [];

  // Entity tokens.
  if (!empty($data[$type]) && $entity_type = token_get_entity_mapping('token', $type)) {
    $entity = $data[$type];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'terms':
          $terms = _taxonomy_entity_index_get_terms_array($entity_type, $entity);
          $replacements[$original] = token_render_array($terms, $options);
          break;
      }
    }

    // Chained token relationships.
    if ($terms_tokens = \Drupal::token()->findWithPrefix($tokens, 'terms')) {
      if ($terms = _taxonomy_entity_index_get_terms_array($entity_type, $entity)) {
        $replacements += \Drupal::token()->generate('array', $terms_tokens, ['array' => $terms], $options);
      }
    }
  }

  return $replacements;
}

/**
 * Helper function to get terms.
 */
function _taxonomy_entity_index_get_terms_array($entity_type, $entity) {
  list($entity_id, $revision_id) = entity_extract_ids($entity_type, $entity);
  // @todo How to properly 'order' these by field/delta?
  $tids = db_query("SELECT tid FROM {taxonomy_entity_index} WHERE entity_type = :type AND entity_id = :id AND revision_id = :vid ORDER BY delta", [
    ':type' => $entity_type,
    ':id' => $entity_id,
    ':vid' => isset($revision_id) ? $revision_id : $entity_id,
  ])->fetchCol();
  $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadMultiple($tids);
  foreach ($terms as $tid => $term) {
    $terms[$tid] = $term->name;
  }
  return $terms;
}
