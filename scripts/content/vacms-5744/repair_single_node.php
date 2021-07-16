<?php

/**
 * @file
 * Processes and repairs the first improperly cloned node.
 */

require_once __DIR__ . '/library.php';

$nid = 2769;
if (empty($nid)) {
  throw new \Exception("C'mon, throw me a bone here. You gotta supply a node ID.");
}
process_node($nid);
