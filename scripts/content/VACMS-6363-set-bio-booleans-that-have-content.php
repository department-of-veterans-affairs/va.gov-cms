<?php

/**
 * @file
 * Set boolean toggle to on on staff profile nodes that have bio content.
 *
 *  VACMS-6363-set-bio-booleans-that-have-content.php.
 */

use Drupal\node\NodeInterface;
use Psr\Log\LogLevel;

$query = \Drupal::entityQuery('node')
  ->condition('type', 'person_profile')
  ->exists('field_intro_text')
  ->exists('field_body');
$entity_ids = $query->execute();
$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($entity_ids);
$count = 0;
foreach ($nodes as $node) {
  // Get current revision mod status to plugin to new revision.
  $current_moderation_status = $node->get('moderation_state')->getString();
  if ($node instanceof NodeInterface) {
    $node->get('moderation_state')->setValue($current_moderation_status);
    // Make this change a new revision.
    $node->setNewRevision(TRUE);
    // Set revision author to uid 1317 (CMS Migrator user).
    $node->setRevisionUserId(1317);
    $node->setChangedTime(time());
    $node->isDefaultRevision(TRUE);
    $node->setRevisionCreationTime(time());
    // Turn on our bio fieldset.
    $node->get('field_complete_biography_create')->setValue(1);
    // Set revision log message.
    $node->setRevisionLogMessage('Resaved with new "Create profile page with biography" boolean checked.');
    $node->save();
    $count++;
  }
}
Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Turned bio display boolean on for %count nodes via set-bio-booleans script.', [
  '%count' => $count,
]);
print("Turned bio display boolean on in '{$count}' nodes.");
