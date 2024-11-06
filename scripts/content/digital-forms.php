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
    'your_personal_info' => [
      'include_dob' => FALSE,
      'include_sn' => FALSE,
    ],
    'additional_steps' => [
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
      [
        'type' => 'digital_form_list_loop',
        'title' => 'Generated List & Loop',
        'optional' => FALSE,
      ],
    ],
  ],
) {
  $digital_form = Node::create($values);

  $digital_form->field_chapters->appendItem(
    your_personal_info($steps['your_personal_info'])
  );

  foreach ($steps['additional_steps'] as $step_values) {
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
    'your_personal_info' => [
      'include_dob' => TRUE,
      'include_sn' => TRUE,
    ],
    'additional_steps' => [
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
      [
        'type' => 'digital_form_list_loop',
        'title' => "Veteran's employment history",
        'optional' => TRUE,
      ],
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
  $step_type = $values['type'];
  $additional_fields = match ($step_type) {
    'digital_form_address' => [
      'field_military_address_checkbox' =>
      $values['military_address_checkbox'] ?? TRUE,
    ],
    'digital_form_list_loop' => ['field_optional' => $values['optional'] ?? FALSE],
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

/**
 * Creates the "Your personal information" Step.
 *
 * @param array $options
 *   An associate array containing options for the nested Paragraph types.
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The created "Your personal information" Step.
 */
function your_personal_info(
  array $options = [
    'include_dob' => TRUE,
    'include_sn' => FALSE,
  ],
): Paragraph {
  $your_personal_info = Paragraph::create(
    ['type' => 'digital_form_your_personal_info']
  );

  $your_personal_info->field_name_and_date_of_birth->appendItem(
    Paragraph::create([
      'type' => 'digital_form_name_and_date_of_bi',
      'field_title' => $options['include_dob']
        ? 'Name and date of birth' : 'Name',
      'field_include_date_of_birth' => $options['include_dob'],
    ])
  );

  $your_personal_info->field_identification_information->appendItem(
    Paragraph::create([
      'type' => 'digital_form_identification_info',
      'field_title' => 'Identification information',
      'field_include_veteran_s_service' => $options['include_sn'],
    ])
  );

  return $your_personal_info;
}
