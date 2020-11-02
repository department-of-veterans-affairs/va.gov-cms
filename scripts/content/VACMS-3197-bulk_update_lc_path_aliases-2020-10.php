<?php

/**
 * @file
 * One-time script to create aliases for lc types.
 *
 *  VACMS-3197-bulk_update_lc_path_aliases-2020-10.php.
 */

use Psr\Log\LogLevel;

$updated = 0;
$failed = 0;

// These are lc bundles.
$types = [
  'type' => ['basic_landing_page',
    'checklist',
    'faq_multiple_q_a',
    'media_list_images',
    'media_list_videos',
    'q_a',
    'step_by_step',
  ],
];

// Load them up.
$entities = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties($types);

// Loop through them and add aliases.
foreach ($entities as $entity) {
  // This is needed to do a force override of all that had opted out.
  // None are published so they should not be opted out.
  $entity->path->pathauto = 1;
  $result = \Drupal::service('pathauto.generator')->updateEntityAlias($entity, 'bulkupdate', ['force' => TRUE]);
  if ($result) {
    Drupal::logger('drush scr')->log(LogLevel::INFO, 'Updated alias for nid %nid completed by VACMS-3197-bulk_update_lc_path_aliases-2020-10.', [
      '%nid' => $entity->id(),
    ]);
    $updated++;
  }
  else {
    Drupal::logger('drush scr')->log(LogLevel::WARNING, 'Skipped or failed updating alias for nid %nid VACMS-3197-bulk_update_lc_path_aliases-2020-10.', [
      '%nid' => $entity->id(),
    ]);
    $failed++;
  }
}
$count = count($entities);
Drupal::logger('drush scr')
  ->log(LogLevel::INFO, 'Path aliases updated %updated out of %count entities. Skipped or failed to update: %failed.', [
    '%count' => $count,
    '%updated' => $updated,
    '%failed' => $failed,
  ]);

print(
  t('Path aliases updated @updated out of @count entities.  Skipped or failed to update: @failed.', [
    '@count' => $count,
    '@updated' => $updated,
    '@failed' => $failed,
  ]));
