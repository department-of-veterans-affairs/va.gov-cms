<?php

/**
 * @file
 * Load up all existing nodes and validate 'em and see what happens.
 */

/**
 * Log a message.
 *
 * @param string $message
 *   The message to log.
 */
function log_message(string $message): void {
  // \Drupal::logger(__FILE__)->notice($message);
  echo PHP_EOL . $message;
}

error_reporting(E_ERROR | E_PARSE);
$nids = \Drupal::entityQuery('node')->execute();
$entity_type_manager = \Drupal::entityTypeManager();
$node_storage = $entity_type_manager->getStorage('node');
$user_storage = $entity_type_manager->getStorage('user');
$uid = 1;
$user = $user_storage->load($uid);
\Drupal::service('account_switcher')
  ->switchTo($user);
log_message("Acting as {$user->getDisplayName()} [{$uid}]");

$chunks = array_chunk($nids, 50);
foreach ($chunks as $chunk_id => $chunk) {
  $nodes = $node_storage->loadMultiple($chunk);
  $count = count($nodes);
  log_message("Loaded {$count} nodes as chunk {$chunk_id}");
  foreach ($nodes as $nid => $node) {
    $violations = $node->validate();
    if (!count($violations)) {
      continue;
    }
    log_message("[{$nid}] => {$node->getTitle()}");
    foreach ($violations as $violation) {
      log_message("\t- {$violation->getPropertyPath()}: {$violation->getMessage()}");
    }
  }
}

log_message('');
