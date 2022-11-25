<?php

/**
 * @file
 * Set the date from a single specified node.
 */

require_once __DIR__ . '/common.php';

switch_user();

$nid = $_SERVER['argv'][3];

$node = get_default_node_revision((int) $nid);
print_header();
print_full_history_of_node($node);

log_message("");
log_message("Updating node $nid...");
set_node_last_human_date($nid);

log_message("");
print_header();
print_full_history_of_node($node);
