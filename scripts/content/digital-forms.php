<?php

/**
 * @file
 * Creates Digital Form test data for local and Tugboat environments.
 *
 *  !!!! DO NOT RUN ON PROD !!!!
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

require_once __DIR__ . '/script-library.php';

run();

/**
 * Executes the script.
 */
function run() {
  $env = getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';
  exit_if_not_local_or_tugboat($env);

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
  $digital_form->field_chapters->appendItem(create_step());
  $digital_form
    ->field_chapters
    ->appendItem(create_step('Step without Date of Birth', FALSE));
  save_node_revision($digital_form, 'Created by the content script', TRUE);
}

/**
 * Creates a Digital Form Step.
 *
 * For now, this only creates the Name and Date of Birth step.
 * That will change as more patterns are added.
 *
 * @param string $title
 *   The step title.
 * @param bool $includeDob
 *   Should the step include the date of birth field?
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The created Step.
 */
function create_step(
  string $title = 'Script Generated Step',
  bool $includeDob = TRUE,
): Paragraph {
  return Paragraph::create([
    'type' => 'digital_form_name_and_date_of_bi',
    'field_title' => $title,
    'field_include_date_of_birth' => $includeDob,
  ]);
}
