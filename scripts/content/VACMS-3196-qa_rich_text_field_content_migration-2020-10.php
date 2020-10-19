<?php

/**
 * @file
 * One-time migration for Q&A content type Answer field paragraphs.
 *
 *  VACMS-3196-qa_rich_text_field_content_migration-2020-10.php.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Psr\Log\LogLevel;

// Ensure that the new configuration is present.
$field_answer_target = \Drupal::config('field.field.node.q_a.field_answer')->get('settings.handler_settings.target_bundles');
if (!isset($field_answer_target['rich_text_char_limit_1000'])) {
  print("The required paragraph configuration is not present, exiting!\n");
  exit(1);
}

$updated = 0;
$failed = 0;
$failed_nids = [];

$qas = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'q_a']);

foreach ($qas as $qa) {
  // Load the old paragraph instance if present.
  $paragraph_old = $qa->get('field_answer')->first()->get('entity')->getTarget()->getValue();

  // Proceed if the paragraph is present and not of the new type.
  if ($paragraph_old instanceof Paragraph && $paragraph_old->bundle() == 'wysiwyg') {
    $text_value = $paragraph_old->get('field_wysiwyg')->getValue()[0]['value'];

    if (isset($text_value)) {
      // Create a new paragraph instance with the previous paragraph's
      // text and connect it to the Q&A node.
      $paragraph_new = Paragraph::create([
        'type' => 'rich_text_char_limit_1000',
        'field_wysiwyg' => [
          'value' => $text_value,
          'format' => 'rich_text_limited',
        ],
      ]);
      $paragraph_new->isNew();
      $paragraph_new->save();

      $new_answer = [
        'target_id' => $paragraph_new->id(),
        'target_revision_id' => $paragraph_new->getRevisionId(),
      ];
      $qa->set('field_answer', $new_answer);
      $qa->setNewRevision(TRUE);
      $qa->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $qa->setRevisionUserId(1317);
      $qa->setRevisionLogMessage('Migration from the old answer to a new answer field with limited characters.');
      $qa->set('moderation_state', 'draft');
      $saved = $qa->save();

      // Delete the old paragraph from the DB.
      $paragraph_old->delete();
    }
  }

  $updated = (is_int($saved) && $saved > 0) ? $updated + 1 : $updated;
  $failed = (is_int($saved) && $saved <= 0) ? $failed + 1 : $failed;
  $failed_nids = (is_int($saved) && $saved <= 0) ? $failed_nids[$qa->id()] : $failed_nids;

  Drupal::logger('va_gov_backend')->log(LogLevel::INFO, 'Migrated field_answer fields content for Q&A node "%id".', [
    '%id' => $qa->id(),
  ]);
}

Drupal::logger('va_gov_backend')
  ->log(LogLevel::INFO, 'Answer values for Q&A nodes were successfully migrated for %updated out of %count entities. %failed %failed_nids', [
    '%count' => count($qas),
    '%updated' => $updated,
    '%failed' => $failed ? 'Failed to update ' . $failed . ' out of %count.' : NULL,
    '%failed_nids' => count($failed_nids) ? 'Failed nids: ' . implode(', ', $failed_nids) . '.' : NULL,
  ]);

print(
  t('Answer values for Q&A nodes were successfully migrated for @updated out of @count entities. @failed @failed_nids', [
    '@count' => count($qas),
    '@updated' => $updated,
    '@failed' => $failed ? 'Failed to update ' . $failed . ' out of %count.' : NULL,
    '@failed_nids' => count($failed_nids) ? 'Failed nids: ' . implode(', ', $failed_nids) . '.' : NULL,
  ])
);
