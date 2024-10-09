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
    'field_expiration_date' => '2024-09-11',
    'field_va_form_number' => '123456789',
    'field_omb_number' => '1234-5678',
    'field_respondent_burden' => 30,
    'moderation_state' => 'published',
  ],
  array $steps = [
    [],
    [
      'type' => 'digital_form_identification_info',
      'title' => 'Generated Identification Information',
      'include_sn' => TRUE,
    ],
    [
      'type' => 'digital_form_address',
      'title' => 'Generated Address',
      'military_address_checkbox' => FALSE,
    ],
    [
      'type' => 'digital_form_phone_and_email',
      'title' => 'Generated Phone',
      'include_email' => FALSE,
    ],
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
    'field_expiration_date' => '2024-07-31',
    'field_va_form_number' => '21-4140',
    'field_omb_number' => '2900-0079',
    'field_respondent_burden' => 5,
    'moderation_state' => 'published',
  ];
  $form_21_4140_steps = [
    [
      'type' => 'digital_form_name_and_date_of_bi',
      'title' => "Veteran's personal information",
      'include_dob' => TRUE,
    ],
    [
      'type' => 'digital_form_identification_info',
      'title' => 'Identification information',
      'include_sn' => TRUE,
    ],
    [
      'type' => 'digital_form_address',
      'title' => "Veteran's mailing information",
      'military_address_checkbox' => TRUE,
    ],
    [
      'type' => 'digital_form_phone_and_email',
      'title' => "Veteran's contact information",
      'include_email' => TRUE,
    ],
  ];

  create_digital_form();
  create_digital_form($form_21_4140, $form_21_4140_steps);
}

/**
 * Creates a Digital Form Step.
 *
 * @param array $values
 *   An associative array containing the values for the Paragraph.
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The created Step.
 */
function create_step(
  array $values = [],
): Paragraph {
  $step_type = $values['type'] ?? 'digital_form_name_and_date_of_bi';
  $additional_fields = match ($step_type) {
    'digital_form_address' => [
      'field_military_address_checkbox' =>
      $values['military_address_checkbox'] ?? TRUE,
    ],
    'digital_form_identification_info' => [
      'field_include_veteran_s_service' => $values['include_sn'] ?? FALSE,
    ],
    'digital_form_name_and_date_of_bi' => [
      'field_include_date_of_birth' => $values['include_dob'] ?? TRUE,
    ],
    'digital_form_phone_and_email' => [
      'field_include_email' => $values['include_email'] ?? TRUE,
    ],
    default => [],
  };
  return Paragraph::create([
    'type' => $step_type,
    'field_title' => $values['title'] ?? 'Script Generated Step',
  ] + $additional_fields);
}
