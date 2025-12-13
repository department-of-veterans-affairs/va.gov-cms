<?php

/**
 * @file
 * Post update file for VA Gov DB.
 */

use Drupal\path_alias\Entity\PathAlias;
use Psr\Log\LogLevel;
use Drupal\node\Entity\Node;
use Drupal\pathauto\PathautoState;

/**
 * Re-save all VAMC system & facility health service nodes.
 */
function va_gov_db_post_update_resave_facility_nodes(&$sandbox) {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  // Get the node count for system/facility health service nodes.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $query = $node_storage->getQuery();
    $group = $query
      ->orConditionGroup()
      ->condition('type', 'health_care_local_health_service')
      ->condition('type', 'regional_health_care_service_des');

    $nids_to_update = $query
      ->condition($group)->accessCheck(FALSE)->execute();
    $result_count = count($nids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    $sandbox['nids_to_update'] = array_combine(
            array_map('_va_gov_db_stringifynid', array_values($nids_to_update)),
            array_values($nids_to_update));
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return t('No health service nodes were found to be processed.');
  }

  $limit = 25;

  // Load entities.
  $node_ids = array_slice($sandbox['nids_to_update'], 0, $limit, TRUE);
  $nodes = $node_storage->loadMultiple($node_ids);

  foreach ($nodes as $node) {
    // Make this change a new revision.
    $node->setNewRevision(TRUE);

    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionAuthorId(1317);
    $node->setChangedTime(time());
    $node->setRevisionCreationTime(time());
    $node->setOwnerId(1317);

    // Set revision log message.
    $node->setRevisionLogMessage('Resaved node to update title and path alias.');
    $node->save();
    unset($sandbox['nids_to_update']["node_{$node->id()}"]);
    $nids[] = $node->id();
    $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Health service nodes %current nodes saved to update the title & alias. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count health service nodes completed by va_gov_db_post_update_resave_facility_nodes.', [
      '%count' => $sandbox['total'],
    ]);
    return "Health service node re-saving complete. {$sandbox['current']} / {$sandbox['total']}";
  }

  return "Processing health service nodes...{$sandbox['current']} / {$sandbox['total']}";
}

/**
 * Strip trailing slashes from redirects.
 */
function va_gov_db_post_update_strip_trailing_redirect_slashes() {
  $connection = \Drupal::database();
  $connection->update('redirect')
    ->condition('redirect_source__path', '%/', 'LIKE')
    ->expression('redirect_source__path', 'TRIM(TRAILING \'/\' FROM redirect_source__path)')
    ->execute();
}

/**
 * Force a single alias per language for all va_form nodes.
 *
 * New path pattern:
 *   /forms/[field_va_form_number].
 */
function va_gov_db_post_update_move_va_form_pages(&$sandbox) {
  if (!isset($sandbox['nids'])) {
    $sandbox['nids'] = \Drupal::entityQuery('node')
      ->condition('type', 'va_form')
      ->accessCheck(FALSE)
      ->execute();
    $sandbox['total'] = count($sandbox['nids']);
    $sandbox['processed'] = 0;
    if (!$sandbox['total']) {
      return t('No va_form nodes found.');
    }
  }

  $nids = array_splice($sandbox['nids'], 0, 100);
  if ($nids) {
    $etm = \Drupal::entityTypeManager();
    $node_storage = $etm->getStorage('node');
    $alias_storage = $etm->getStorage('path_alias');

    foreach ($node_storage->loadMultiple($nids) as $node) {
      $system_path = '/node/' . $node->id();

      // Build once; used for all translations (pattern doesn't vary by lang).
      if (!$node->hasField('field_va_form_number') || $node->get('field_va_form_number')
        ->isEmpty()) {
        // Skip nodes missing the source field.
        continue;
      }
      $form_number = trim((string) $node->get('field_va_form_number')->value);
      if ($form_number === '') {
        continue;
      }
      $alias_value = '/forms/' . $form_number;

      // Enforce "sole" alias: delete all existing aliases for this node (any
      // lang).
      $existing_all_langs = $alias_storage->loadByProperties(['path' => $system_path]);
      if ($existing_all_langs) {
        $alias_storage->delete($existing_all_langs);
      }

      // Create exactly one alias per translation language.
      foreach ($node->getTranslationLanguages() as $langcode => $language) {
        PathAlias::create([
          'path' => $system_path,
          'alias' => $alias_value,
          'langcode' => $langcode,
        ])->save();
      }
    }

    $sandbox['processed'] += count($nids);
  }

  if ($sandbox['processed'] < $sandbox['total']) {
    $sandbox['#finished'] = $sandbox['processed'] / $sandbox['total'];
    return t('Processed @done / @total va_form nodes.', [
      '@done' => $sandbox['processed'],
      '@total' => $sandbox['total'],
    ]);
  }

  return t('Finished changing va_form aliases to /forms/[field_va_form_number].');
}

/**
 * Move /find-forms to /forms for Centralized Forms initiative.
 */
function va_gov_db_post_update_move_find_forms() {
  require_once DRUPAL_ROOT . '/../scripts/content/script-library.php';
  $node = Node::load(2352);
  $node->setRevisionUserId(1317);
  $node->set('path', [
    'alias' => '/forms',
    'pathauto' => PathautoState::SKIP,
    'langcode' => 'en',
  ]);
  save_node_revision($node, 'Updated path alias from /find-forms to /forms for Centralized Forms initiative.', TRUE);
}

/**
 * Rollback va_gov_db_post_update_move_find_forms().
 */
function va_gov_db_post_update_move_va_form_pages_rollback(&$sandbox) {
  if (!isset($sandbox['nids'])) {
    $sandbox['nids'] = \Drupal::entityQuery('node')
      ->condition('type', 'va_form')
      ->accessCheck(FALSE)
      ->execute();
    $sandbox['total'] = count($sandbox['nids']);
    $sandbox['processed'] = 0;
    if (!$sandbox['total']) {
      return t('No va_form nodes found.');
    }
  }

  $nids = array_splice($sandbox['nids'], 0, 100);
  if ($nids) {
    $etm = \Drupal::entityTypeManager();
    $node_storage = $etm->getStorage('node');
    $alias_storage = $etm->getStorage('path_alias');

    foreach ($node_storage->loadMultiple($nids) as $node) {
      $system_path = '/node/' . $node->id();

      // Build once; used for all translations (pattern doesn't vary by lang).
      if (!$node->hasField('field_va_form_number') || $node->get('field_va_form_number')
          ->isEmpty()) {
        // Skip nodes missing the source field.
        continue;
      }
      $form_number = trim((string) $node->get('field_va_form_number')->value);
      if ($form_number === '') {
        continue;
      }
      $alias_value = '/find-forms/about-form-' . $form_number;

      // Enforce "sole" alias: delete all existing aliases for this node (any
      // lang).
      $existing_all_langs = $alias_storage->loadByProperties(['path' => $system_path]);
      if ($existing_all_langs) {
        $alias_storage->delete($existing_all_langs);
      }

      // Create exactly one alias per translation language.
      foreach ($node->getTranslationLanguages() as $langcode => $language) {
        PathAlias::create([
          'path' => $system_path,
          'alias' => $alias_value,
          'langcode' => $langcode,
        ])->save();
      }
    }

    $sandbox['processed'] += count($nids);
  }

  if ($sandbox['processed'] < $sandbox['total']) {
    $sandbox['#finished'] = $sandbox['processed'] / $sandbox['total'];
    return t('Processed @done / @total va_form nodes.', [
      '@done' => $sandbox['processed'],
      '@total' => $sandbox['total'],
    ]);
  }

  return t('Finished changing va_form aliases to /find-forms/about-form-[field_va_form_number].');
}

/**
 * Rollback va_gov_db_post_update_move_find_forms_rollback().
 */
function va_gov_db_post_update_move_find_forms_rollback() {
  require_once DRUPAL_ROOT . '/../scripts/content/script-library.php';
  $node = Node::load(2352);
  $node->setRevisionUserId(1317);
  $node->set('path', [
    'alias' => '/find-forms',
    'pathauto' => PathautoState::SKIP,
    'langcode' => 'en',
  ]);
  save_node_revision($node, 'Rollback: Updated path alias from /forms to /find-forms for Centralized Forms initiative.', TRUE);
}

/**
 * Callback function to concat node ids with string.
 *
 * @param int $nid
 *   The node id.
 *
 * @return string
 *   The node id concatenated to the end o node_
 */
function _va_gov_db_stringifynid($nid) {
  return "node_$nid";
}
