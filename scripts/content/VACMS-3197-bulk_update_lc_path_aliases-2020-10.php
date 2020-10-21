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
  $result = \Drupal::service('pathauto.generator')->updateEntityAlias($entity, 'bulkupdate', ['force' => TRUE]);
  if ($result) {
    // Write the nid to our log.
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Updated alias for nid %nid completed by va_gov_db_update_8020.', [
      '%nid' => $entity->id(),
    ]);
    $updated = $updated++;
  }
  else {
    $failed = $failed++;
  }
}

Drupal::logger('va_gov_db')
  ->log(LogLevel::INFO, 'Path aliases were successfully updated for %updated out of %count entities. %failed', [
    '%count' => count($entities),
    '%updated' => $updated,
    '%failed' => $failed ? 'Failed to update ' . $failed . ' out of %count.' : NULL,
  ]);

print(
  t('Path aliases were successfully updated for @updated out of %count entities. @failed', [
    '@count' => count($entities),
    '@updated' => $updated,
    '@failed' => $failed ? 'Failed to update ' . $failed . ' out of %count.' : NULL,
  ]));
