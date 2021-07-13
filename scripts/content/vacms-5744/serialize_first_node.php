<?php

/**
 * @file
 * Usable to quickly view the serialized output of the first problematic node.
 */

require_once __DIR__ . '/library.php';

$nids = get_currently_improperly_cloned_nodes();
$nid = $nids[0];
$node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
$json = get_node_serialization($node);
log_message($json);
