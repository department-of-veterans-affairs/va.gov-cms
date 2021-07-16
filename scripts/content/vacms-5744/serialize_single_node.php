<?php

/**
 * @file
 * Usable to quickly view the serialized output of a node.
 */

require_once __DIR__ . '/library.php';

$nid = 589;
if (empty($nid)) {
  throw new \Exception("C'mon, throw me a bone here. You gotta supply a node ID.");
}
$node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
$json = get_node_serialization($node);
log_message($json);
