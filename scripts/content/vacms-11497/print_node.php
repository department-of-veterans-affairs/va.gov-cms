<?php

/**
 * @file
 * Show the date from a single specified node.
 */

require_once __DIR__ . '/common.php';

$nid = $_SERVER['argv'][3];

switch_user();

$node = get_default_node_revision((int) $nid);
print_header();
print_node($node);
