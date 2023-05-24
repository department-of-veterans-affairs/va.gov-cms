<?php

/**
 * @file
 * Load the queue full of nodes to process.
 */

require_once __DIR__ . '/common.php';

switch_user();

delete_queue();
