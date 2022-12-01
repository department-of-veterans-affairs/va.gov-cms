<?php

/**
 * @file
 * Run the queue, processing a chunk of nodes.
 */

require_once __DIR__ . '/common.php';

switch_user();

if (!run_queue()) {
  die('No more queue items to process!');
}
