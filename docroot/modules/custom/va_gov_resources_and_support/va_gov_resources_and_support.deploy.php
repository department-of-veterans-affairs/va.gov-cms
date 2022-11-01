<?php

/**
 * @file
 * Deploy hooks for va_gov_resources_and_support.
 *
 * This is a NAME.deploy.php file. It contains "deploy" functions. These are
 * one-time functions that run *after* config is imported during a deployment.
 * These are a higher level alternative to hook_update_n and
 * hook_post_update_NAME functions.
 *
 * See https://www.drush.org/latest/deploycommand/#authoring-update-functions
 * for a detailed comparison.
 */

require_once __DIR__ . '/../../../../scripts/content/script-library.php';

use Drupal\node\Entity\Node;
use Psr\Log\LogLevel;
use Drupal\taxonomy\Entity\Term;

/**
 * Migrate Publication field_benefits data to new field_lc_categories field.
 */
function va_gov_resources_and_support_deploy_create_field_lc_categories(&$sandbox) {
  // Run initial entity query and store batch variables.
  if (empty($sandbox['total'])) {
    $sandbox['nids_process'] = \Drupal::entityQuery('node')
      ->condition('type', 'outreach_asset')
      ->execute();
    $sandbox['total'] = count($sandbox['nids_process']);
    $sandbox['current'] = 0;
    $sandbox['revision_message'] = 'Migrate data from field_benefits to field_lc_categories.';
    /** @var \Drupal\taxonomy\TermStorageInterface $term_storage */
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    // Add all existing R&S Terms to $sandbox to avoid re-querying each time.
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'lc_categories')
      ->execute();
    if (is_array($tids) && !empty($tids)) {
      $terms = $term_storage->loadMultiple($tids);
      foreach ($terms as $term) {
        $sandbox['terms'][$term->id()] = $term->get('name')->value;
      }
    }
  }

  $options = [
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
    'pact' => 'PACT Act',
    'pension' => 'Pension',
    'records' => 'Records',
    'service' => 'Service member benefits',
    'account' => 'VA account and profile',
    'other' => 'Other topics and questions',
  ];

  // Execute in batches of 25.
  $i = 0;
  $nids = '';
  foreach ($sandbox['nids_process'] as $revision => $nid) {
    if ($i == 25) {
      break;
    }
    $node = Node::load($nid);
    $field_lc_categories = [];
    foreach ($node->get('field_benefits') as $delta => $field_benefits_value) {
      // Attempt to locate an R&S Category term with this name.
      if (isset($options[$field_benefits_value->value])) {
        $option_value = $options[$field_benefits_value->value];
        if (in_array($option_value, $sandbox['terms'])) {
          $field_lc_categories[$delta] = array_search($option_value, $sandbox['terms']);
        }
        else {
          Drupal::logger('va_gov_resources_and_support')
            ->log(LogLevel::INFO, "Unable to populate Publication's field_lc_categories field: No matching term found for %option in node id %nid", [
              '%current' => $sandbox['current'],
              '%nid' => $node->id(),
            ]);
        }
      }
    }

    $node->set('field_lc_categories', $field_lc_categories);
    // Grab the latest revision before we save this one.
    $nvid = $node->getRevisionId();
    $latest_revision = get_node_at_latest_revision($nid);
    save_node_revision($node, $sandbox['revision_message']);
    // If a revision (draft) newer than the default exists, update it as well.
    if ($nvid !== $latest_revision->getRevisionId()) {
      $latest_revision->set('field_lc_categories', $field_lc_categories);
      save_node_revision($latest_revision, $sandbox['revision_message']);
      unset($latest_revision);
    }
    unset($sandbox['nids_process'][$revision]);
    $i++;
    $nids .= $nid . ', ';
    $sandbox['current']++;
  }

  // Tell drupal we processed some nodes.
  Drupal::logger('va_gov_resources_and_support')
    ->log(LogLevel::INFO, 'Publication nodes %current nodes saved to migrate related benefits data. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => $nids,
    ]);

  // Determine when to stop batching.
  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  // Log the all finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_resources_and_support')->log(LogLevel::INFO, 'Updating %count Publication nodes completed by va_gov_resources_and_support_deploy_create_field_lc_categories().', [
      '%count' => $sandbox['total'],
    ]);
    return "Process complete.";
  }

  return "Processing publication nodes...";
}

/**
 * Populate new R&S Taxonomy fields field_topic_id & field_enforce_unique_value.
 */
function va_gov_resources_and_support_deploy_populate_field_topic_id_terms(&$sandbox) {
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
    'pact' => 'PACT Act',
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
      else {
        Drupal::logger('va_gov_resources_and_support')->log(LogLevel::WARNING, 'Unable to load term with term id: %tid during va_gov_resources_and_support_deploy_populate_field_topic_id_terms() deploy hook processing.', [
          '%tid' => $tid,
        ]);
      }
    }
  }
}
