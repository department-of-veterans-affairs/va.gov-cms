<?php

/**
 * @file
 * Show the date from a single specified node.
 */

require_once __DIR__ . '/common.php';

$nid = $_SERVER['argv'][3];

switch_user();

$node = get_node_at_default_revision((int) $nid);
print_header();
print_node($node);
