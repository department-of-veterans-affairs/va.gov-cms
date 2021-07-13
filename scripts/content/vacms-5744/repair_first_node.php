<?php

/**
 * @file
 * Processes and repairs the first improperly cloned node.
 */

require_once __DIR__ . '/library.php';

$nids = get_currently_improperly_cloned_nodes();
$nid = $nids[0];
process_node($nid);
