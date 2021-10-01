<?php

/**
 * @file
 * Set default value for EHR on VAMC system nodes.
 *
 *  VACMS-6573-set-default-ehr-on-systems.php.
 */

use Drupal\node\NodeInterface;
use Psr\Log\LogLevel;

$query = \Drupal::entityQuery('node')
  ->condition('type', 'health_care_region_page');
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
    // Set our default EHR to vista.
    $ehr_val = $node->id() !== '15038' ? 'vista' : 'cerner';
    $node->get('field_vamc_ehr_system')->setValue($ehr_val);
    // Set revision log message.
    $node->setRevisionLogMessage('Resaved with default value for "Electronic Health Records system" field.');
    $node->save();
    $count++;
  }
}
Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Set default EHR value on for %count nodes via set-default-ehr script.', [
  '%count' => $count,
]);
print("Set default EHR on in '{$count}' nodes.");
