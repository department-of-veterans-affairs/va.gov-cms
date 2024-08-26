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

  create_digital_forms();
}

/**
 * Creates a Digital Form with hard-coded values.
 *
 * @param array $values
 *   The Node values.
 * @param array $steps
 *   An array of arrays. The inner arrays contain step values.
 */
function create_digital_form(
  array $values = [
    'type' => 'digital_form',
    'title' => 'Script Generated Digital Form',
    'field_va_form_number' => '123456789',
    'field_omb_number' => '1234-5678',
    'moderation_state' => 'published',
  ],
  array $steps = [
    [],
    ['title' => 'Step without Date of Birth', 'includeDob' => FALSE],
  ],
) {
  $digital_form = Node::create($values);

  foreach ($steps as $step_values) {
    $digital_form->field_chapters->appendItem(create_step($step_values));
  }

  save_node_revision($digital_form, 'Created by the content script', TRUE);
}

/**
 * Creates test Digital Forms.
 */
function create_digital_forms() {
  $form_21_4140 = [
    'type' => 'digital_form',
    'title' => 'Employment Questionnaire',
    'field_va_form_number' => '21-4140',
    'field_omb_number' => '2900-0079',
    'moderation_state' => 'published',
  ];
  $form_21_4140_steps = [
    ['title' => "Veteran's personal information", 'includeDob' => TRUE],
  ];

  create_digital_form();
  create_digital_form($form_21_4140, $form_21_4140_steps);
}

/**
 * Creates a Digital Form Step.
 *
 * For now, this only creates the Name and Date of Birth step.
 * That will change as more patterns are added.
 *
 * @param array $values
 *   The values for the Paragraph.
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The created Step.
 */
function create_step(
  array $values = [],
): Paragraph {
  return Paragraph::create([
    'type' => 'digital_form_name_and_date_of_bi',
    'field_title' => $values['title'] ?? 'Script Generated Step',
    'field_include_date_of_birth' => $values['includeDob'] ?? TRUE,
  ]);
}
