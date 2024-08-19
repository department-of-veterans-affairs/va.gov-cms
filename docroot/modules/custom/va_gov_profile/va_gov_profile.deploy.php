<?php
/**
 * @file
 * Deploy hooks for va_gov_profile.
 *
 * This is a NAME.deploy.php file. It contains "deploy" functions. These are
 * one-time functions that run *after* config is imported during a deployment.
 * These are a higher level alternative to hook_update_n and
 * hook_post_update_NAME functions.
 *
 * See https://www.drush.org/latest/deploycommand/#authoring-update-functions
 * for a detailed comparison.
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

require_once __DIR__ . '/../../../../scripts/content/script-library.php';

/**
 * Implements hook_deploy_NAME().
 */
function va_gov_profile_deploy_move_phone_to_paragraph(array &$sandbox) {
  // Run initial entity query and store batch variables.
  if (empty($sandbox['total'])) {
    $sandbox['nids_process'] = \Drupal::entityQuery('node')
      ->condition('type', 'outreach_asset')
      ->accessCheck(FALSE)
      ->execute();
    $sandbox['total'] = count($sandbox['nids_process']);
    $sandbox['current'] = 0;
    $sandbox['revision_message'] = 'Migrate from telephone field to telephone paragraph.';
  }

  // Execute in batches of 25.
  $i = 0;
  $nids = '';
  foreach ($sandbox['nids_process'] as $revision => $nid) {
    if ($i == 25) {
      break;
    }
    $node = Node::load($nid);
    $field_name = 'field_phone_number';
    $paragraph_field_name = 'field_phone';
    $paragraphs = [];
    foreach ($node->get($field_name) as $delta => $field_value) {
    // Create paragraph from existing values.
      $paragraphs[] = Paragraph::create([
        'bundle' => 'phone_number',
        'field_phone_number' => $field_value,
      ])->save();
    }

    // Set the new phone value(s) on the node.
    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $node->set($paragraph_field_name, value: array_map(callback: fn($paragraph) => [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ], array: $paragraphs));

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
  Drupal::logger('va_gov_profile')
    ->log(LogLevel::INFO, 'Staff Profile nodes %current nodes saved to migrate phone data. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => $nids,
    ]);

  // Determine when to stop batching.
  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  // Log the all finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_profile')->log(LogLevel::INFO, 'Updating %count Staff Profile nodes completed by va_gov_profile_deploy_move_phone_to_paragraph().', [
      '%count' => $sandbox['total'],
    ]);
    return "Process complete.";
  }

  return "Processing Staff Profile nodes...";
}
