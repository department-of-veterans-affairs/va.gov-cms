<?php

/**
 * @file
 * Drush deploy hooks for va_gov_banner.
 */

declare(strict_types=1);

use Drupal\expirable_content\EntityOperations;

/**
 * Implements hook_deploy_NAME().
 */
function va_gov_banner_deploy_seed_fwb_expiration(array &$sandbox) {
  // This deploy hook serves to "seed" the initial expiration dates for
  // VACMS-15506.
  /** @var \Drupal\expirable_content\EntityOperations $entityOperations */
  $entityOperations = \Drupal::service('class_resolver')
    ->getInstanceFromDefinition(EntityOperations::class);
  // We only expect one or two nodes, so no need to batch operate.
  $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
    'type' => 'banner',
    'status' => 1,
    'moderation_state' => 'published',
  ]);
  foreach ($nodes as $node) {
    try {
      $entityOperations->entityInsert($node);
    }
    catch (\Exception $e) {
      \Drupal::logger('va_gov_banner')->error(sprintf('Could not create new Expirable Content entity for node id: %nid. The error was: <pre>%error</pre>', [
        '%nid' => $node->id(),
        '%error' => $e,
      ]));
    }
  }
}
