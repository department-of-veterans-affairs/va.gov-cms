<?php

/**
 * @file vaThis is a NAME.deploy.php file. It contains "deploy" functions. These are
 * one-time functions that run *after* config is imported during a deployment.
 * These are a higher level alternative to hook_update_n and hook_post_update_NAME
 * functions. See https://www.drush.org/latest/deploycommand/#authoring-update-functions
 * for a detailed comparison.
 */

use Drupal\node\Entity\Node;
use Psr\Log\LogLevel;

/**
 * Migrate Publication field_benefits data to new field_lc_categories field.
 */
function va_gov_db_deploy_create_field_lc_categories(&$sandbox) {
  // Run initial entity query and store batch variables.
  if (empty($sandbox['total'])) {
    $sandbox['nids_process'] = \Drupal::entityQuery('node')
      ->condition('type', 'outreach_asset')
      ->execute();
    $sandbox['total'] = count($sandbox['nids_process']);
    $sandbox['current'] = 0;
  }

  $options = [
    'general' => 'General Benefits Information',
    'burial' => 'Burial Benefits And Memorial Items',
    'careers' => 'Careers And Employment',
    'disability' => 'Disability Compensation',
    'education' => 'Education And Training Benefits',
    'family' => 'Family member benefits',
    'healthcare' => 'Health Care',
    'housing' => 'Housing Assistance',
    'insurance' => 'Life Insurance',
    'pension' => 'Pension Benefits',
    'service' => 'Service Member Benefits',
    'records' => 'Records',
  ];

  // Execute in batches of 25.
  $i = 0;
  $nids = '';
  foreach ($sandbox['nids_process'] as $revision => $nid) {
    if ($i == 25) {
      break;
    }
    $node = Node::load($nid);
    $node->setNewRevision(TRUE);
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->isDefaultRevision(TRUE);
    $node->setRevisionCreationTime(time());
    $node->setOwnerId(1317);
    $node->setRevisionLogMessage('Migrate data from field_benefits to field_lc_categories.');
    $field_lc_categories = [];
    foreach ($node->get('field_benefits') as $delta => $field_benefits_value) {
      // Attempt to locate an R&S Category term with this name.
      $tid = \Drupal::entityQuery('taxonomy_term')
        ->condition('name', $options[$field_benefits_value->value])
        ->condition('vid', 'lc_categories')
        ->execute();
      if (!empty($tid)) {
        $field_lc_categories[$delta] = reset($tid);
      }
    }

    $node->set('field_lc_categories', $field_lc_categories);
    $node->save();
    unset($sandbox['nids_process'][$revision]);
    $i++;
    $nids .= $nid . ', ';
    $sandbox['current']++;
  }

  // Tell drupal we processed some nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Publication nodes %current nodes saved to migrate related benefits data. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => $nids,
    ]);

  // Determine when to stop batching.
  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  // Log the all finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Updating %count Publication nodes completed by va_gov_db_update_9010.', [
      '%count' => $sandbox['total'],
    ]);
    return "Process complete.";
  }

  return "Processing publication nodes...";
}


/**
 * Populate new R&S Taxonomy fields field_topic_id & field_enforce_unique_value.
 */
function va_gov_db_deploy_populate_field_topic_id_terms(&$sandbox) {
  $values = [
    'burial' => 'Burials and memorials',
    'careers' => 'Careers and employment',
    'decision' => 'Decision reviews and appeals',
    'disability' => 'Disability',
    'education' => 'Education and training',
    'family' => 'Family member benefits',
    'healthcare' => 'Health care',
    'housing' => 'Housing assistance and home loans',
    'general' => 'General benefits information',
    'insurance' => 'Life insurance',
    'pension' => 'Pension',
    'records' => 'Records',
    'service' => 'Service member benefits',
    'account' => 'VA account and profile',
    'other' => 'Other topics and questions',
  ];

  foreach ($values as $machine_name => $name) {
    // Attempt to locate an R&S Category term with this name.
    $tid = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $name)
      ->condition('vid', 'lc_categories')
      ->execute();
    if (!empty($tid)) {
      $term = Term::load(reset($tid));
      if ($term) {
        $term->set('field_topic_id', $machine_name);
        $term->save();
      }
    }
  }
}
