<?php

/**
 * @file
 * Creates Digital Form test data for local and Tugboat environments.
 *
 *  !!!! DO NOT RUN ON PROD !!!!
 */

use Drupal\node\Entity\Node;

require_once __DIR__ . '/script-library.php';

run();

/**
 * Executes the script.
 */
function run() {
  $env = getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';
  exit_if_wrong_env($env);

  create_digital_form();
}

/**
 * Creates a Digital Form with hard-coded values.
 */
function create_digital_form() {
  $digital_form = Node::create([
    'type' => 'digital_form',
    'title' => 'Script Generated Digital Form',
    'field_va_form_number' => '123456789',
    'field_omb_number' => '1234-5678',
    'moderation_state' => 'published',
  ]);
  save_node_revision($digital_form, 'Created by the content script', TRUE);
}
