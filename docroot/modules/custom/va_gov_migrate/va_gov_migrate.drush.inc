<?php

/**
 * @file
 * Drush jobs to clean up old revisions.
 */

use Drupal\Core\Entity\EntityStorageException;
use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function va_gov_migrate_drush_command() {
  $items = [];

  $items['va-gov-clean-revs'] = [
    'description' => 'Clean up bad node revisions',
    'aliases' => ['vg-cr'],
  ];

  return $items;
}

/**
 * Command for cleaning up revisions.
 *
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
 */
function drush_va_gov_migrate_va_gov_clean_revs() {
  $time = 1572551719;

  $query = \Drupal::database()->select('node_revision', 'nr');
  $query->condition('revision_timestamp', $time);
  $query->fields('nr', ['vid']);
  $vids = $query->execute()->fetchCol();

  $query = \Drupal::database()->select('node_revision', 'nr');
  $query->condition('revision_log', 'Update of status by migration.');
  $query->condition('nid', 1884);
  $query->fields('nr', ['vid']);
  $more_vids = $query->execute()->fetchCol();

  $vids = array_merge($vids, $more_vids);

  drush_log('Attempting to Delete ' . count($vids) . ' node revisions', LogLevel::INFO);
  $missed_vids = [];
  foreach ($vids as $vid) {
    try {
      drush_log('Deleting: ' . $vid, LogLevel::INFO);
      \Drupal::entityTypeManager()->getStorage('node')->deleteRevision($vid);
    }
    catch (EntityStorageException $e) {
      drush_log('Vid ' . $vid . ' could not be deleted: ' . $e->getMessage(), LogLevel::WARNING);
      $missed_vids[] = $vid;
    }
  }

  $count = count($vids) - count($missed_vids);
  drush_log('Deleted ' . $count . ' revision', LogLevel::OK);
  drush_log('The following revisions were not deleted: ' . implode(', ', $missed_vids), LogLevel::WARNING);
}
