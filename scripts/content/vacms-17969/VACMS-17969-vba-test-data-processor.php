<?php

/**
 * @file
 * Creates VBA test data for local or Tugboat environments only.
 *
 * !!!!! DO NOT RUN ON PROD. !!!!!
 */

use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

require_once __DIR__ . '../../script-library.php';

run();

/**
 * Executes the script using the CSV files and callback functions.
 */
function run() {
  $env = getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';
  exit_if_not_local_or_tugboat($env);
  process_csv_file(__DIR__ . '/VACMS-17969-vba-test-data-source-services.csv',
    'create_vba_facility_service_node');
  process_csv_file(__DIR__ . '/VACMS-17969-vba-test-data-source-facilities.csv',
    'update_vba_facility_node');
  process_csv_file(__DIR__ . '/VACMS-17969-vba-test-data-source-service-regions.csv',
    'create_vba_service_region_node');
}

/**
 * Process a CSV file.
 *
 * @param string $csv_file_path
 *   The path to the CSV file.
 * @param string $process_row_function
 *   The function to process each row in the CSV file.
 */
function process_csv_file($csv_file_path, $process_row_function) {
  if (($handle = fopen($csv_file_path, 'r')) !== FALSE) {
    // Read and discard the header row.
    fgetcsv($handle);
    // Read the rest of the CSV file.
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      // Each row in the CSV file becomes an array in $data.
      // The array and CSV are in the same order.
      // Process a row.
      $process_row_function($data);
    }
    // Close the CSV file.
    fclose($handle);
  }
}

/**
 * Update a VBA facility node.
 *
 * @param array $data
 *   The data from the CSV file.
 */
function update_vba_facility_node($data) {
  $node = \Drupal::entityTypeManager()->getStorage('node')->load($data[1]);
  $node->field_show_banner->value = $data[2];
  $node->field_alert_type->value = $data[3];
  $node->field_dismissible_option->value = $data[4];
  $node->field_banner_title->value = $data[5];
  $node->field_banner_content->value = $data[6];
  $node->field_operating_status_facility->value = $data[7];
  $node->field_operating_status_more_info->value = $data[8];
  add_prepare_for_your_visit_to_facility($node, $data[9]);
  add_media_to_facility($node, $data[12]);
  add_spotlights_to_facility($node, $data[13], $data[16]);
  $node->moderation_state = 'published';
  save_node_revision($node, "Updated for VBA test data", TRUE);
}

/**
 * Create a VBA service region node.
 *
 * @param array $data
 *   The data from the CSV file.
 */
function create_vba_service_region_node($data) {
  $service_region = Node::create([
    'type' => 'service_region',
    'title' => $data[28],
    'field_service_name_and_descripti' => [
      'target_id' => $data[1],
    ],
    'field_administration' => [
      'target_id' => $data[3],
    ],
    'field_facility_location' => [
      'target_id' => $data[30],
    ],
    'moderation_state' => 'published',
  ]);
  // Create the service location paragraph(s).
  $number_of_service_locations = $data[6];
  for ($i = 0; $i < $number_of_service_locations; $i++) {
    $service_location = create_service_location_paragraph($data, $i);
    $service_region->field_service_location->appendItem($service_location);
  }
  save_node_revision($service_region, "Created for VBA test data", TRUE);
}

/**
 * Create a VBA facility service node.
 *
 * @param array $data
 *   The data from the CSV file.
 */
function create_vba_facility_service_node($data) {
  $facility_id = $data[1];
  $facility_section = $data[3];
  $service_id = $data[5];

  // Create the VBA service node initially
  // and then add to it the necessary service locations.
  $service_node = Node::create([
    'type' => 'vba_facility_service',
    'field_administration' => [
      'target_id' => $facility_section,
    ],
    'field_office' => [
      'target_id' => $facility_id,
    ],
    'field_service_name_and_descripti' => [
      'target_id' => $service_id,
    ],
    'moderation_state' => 'published',
  ]);
  $service_node->save();

  // Create the service location paragraph(s).
  $number_of_service_locations = $data[6];
  for ($i = 0; $i < $number_of_service_locations; $i++) {
    $service_location = create_service_location_paragraph($data, $i);
    $service_node->field_service_location->appendItem($service_location);
  }
  save_node_revision($service_node, "Created for VBA test data", TRUE);
}

/**
 * Create service location paragraphs.
 *
 * @param array $data
 *   The data from the CSV file.
 * @param int $service_location_index
 *   The number of the service location creation loop.
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The service location paragraph.
 */
function create_service_location_paragraph($data, $service_location_index) {
  // If this is the first service location, use the data from the CSV file.
  if ($service_location_index === 0) {
    $service_location = Paragraph::create([
      'type' => 'service_location',
      'field_office_visits' => $data[7],
      'field_virtual_support' => $data[8],
      'field_appt_intro_text_type' => $data[9],
      'field_appt_intro_text_custom' => $data[10] . ' for service location ' . $service_location_index + 1,
      'field_use_facility_phone_number' => $data[11],
      'field_online_scheduling_avail' => $data[15],
    ]);
    $number_of_appointment_phones = $data[12];
    $service_location->field_use_main_facility_phone = $data[23];
    $number_of_contact_phones = $data[24];
    $number_of_contact_emails = $data[27];
  }
  else {
    // If not, generate random data.
    $field_appt_intro_text_type = choose_random_appt_intro_text_type();
    if ($field_appt_intro_text_type === 'customize_text') {
      $field_appt_intro_text_custom = 'Random text for service location ' . $service_location_index + 1;
    }
    else {
      $field_appt_intro_text_custom = NULL;
    }
    $service_location = Paragraph::create([
      'type' => 'service_location',
      'field_office_visits' => choose_random_office_visit(),
      'field_virtual_support' => choose_random_virtual_support(),
      'field_appt_intro_text_type' => $field_appt_intro_text_type,
      'field_appt_intro_text_custom' => $field_appt_intro_text_custom,
      'field_use_facility_phone_number' => rand(0, 1),
      'field_online_scheduling_avail' => choose_random_online_scheduling_avail_option(),
    ]);
    $number_of_appointment_phones = rand(0, 9);
    $service_location->field_use_main_facility_phone = choose_random_true_false_null();
    $number_of_contact_phones = rand(0, 5);
    $number_of_contact_emails = rand(0, 5);
  }

  $service_location->save();

  create_service_location_phone_numbers($service_location, 'field_other_phone_numbers', $number_of_appointment_phones, $data[13], $data[14]);
  $service_location->field_service_location_address->appendItem(create_service_location_address_paragraph($data));
  $service_location->field_hours = $data[20];
  $service_location->field_additional_hours_info = $data[22];
  // If "specify hours" is selected, create office hours.
  if ($data[20] === '2') {
    $service_location->field_office_hours = create_service_location_hours($data[21]);
  }
  create_service_location_phone_numbers($service_location, 'field_phone', $number_of_contact_phones, $data[25], $data[26]);
  create_email_contacts($service_location, 'field_email_contacts', $number_of_contact_emails);
  $service_location->save();

  return $service_location;
}

/**
 * Create phone numbers for a service location.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $service_location
 *   The service location paragraph.
 * @param string $paragraph_field
 *   The paragraph field to which the phone numbers will be appended.
 * @param int $number_of_phone_numbers
 *   The number of phone numbers to create.
 * @param string $phone_type
 *   The type of phone number. (e.g., 'tel', 'fax', 'sms', 'tty')
 * @param int $create_extension
 *   Whether to create an extension for the first phone number.
 */
function create_service_location_phone_numbers($service_location, $paragraph_field, $number_of_phone_numbers, $phone_type, $create_extension) {
  // There can be multiple appointment phone numbers for a service location.
  // We want to ensure that the first appointment phone number is of the
  // desired type and has an extension, as specified in the CSV file.
  // The rest of the appointment phone numbers can be of a random type
  // with random extensions or no extension.
  $phone_numbers = NULL;
  for ($i = 0; $i < $number_of_phone_numbers; $i++) {
    if ($i === 0) {
      $service_location->get($paragraph_field)->appendItem(create_phone_paragraph($i, $phone_type, (bool) $create_extension));
    }
    else {
      $service_location->get($paragraph_field)->appendItem(create_phone_paragraph($i, choose_random_phone_number_type(), (bool) rand(0, 1)));
    }
  }

  return $phone_numbers;
}

/**
 * Create a phone paragraph.
 *
 * @param int $phone_index
 *   The index of the loop to create multiple phone numbers.
 * @param string $phone_type
 *   The type of phone number. (e.g., 'tel', 'fax', 'sms', 'tty')
 * @param bool $add_extension
 *   Whether to add an extension to the phone number.
 */
function create_phone_paragraph($phone_index, $phone_type, $add_extension = NULL) {
  $phone_number = generate_random_phone_number();

  // We only want an extension when the phone type is 'tel'.
  if ($phone_type === 'tel') {
    $random_extension = (string) rand(1, 99999);
    $add_extension = (is_null($add_extension)) ? rand(0, 1) : $add_extension;
  }
  else {
    $random_extension = NULL;
    $add_extension = NULL;
  }
  $extension = $add_extension ? $random_extension : NULL;

  $phone = Paragraph::create([
    'type' => 'phone_number',
    'field_phone_number' => $phone_number,
    'field_phone_number_type' => $phone_type,
    'field_phone_extension' => $extension,
    'field_phone_label' => sprintf("Service location phone %d, (%s)", $phone_index + 1, $phone_type),
  ]);
  $phone->save();
  return $phone;
}

/**
 * Create a service location address paragraph.
 *
 * @param array $data
 *   The data from the CSV file.
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The service location address paragraph.
 */
function create_service_location_address_paragraph(array $data) {
  $service_location_address = Paragraph::create([
    'type' => 'service_location_address',
    'field_clinic_name' => $data[16],
    'field_building_name_number' => $data[17],
    'field_wing_floor_or_room_number' => $data[18],
    'field_use_facility_address' => $data[19],

  ]);
  if ($data[19] === '0') {
    $service_location_address->field_address->setValue([
      'country_code' => 'US',
      'administrative_area' => 'NE',
      'locality' => 'Omaha',
      'address_line1' => '1010 Dodge Street',
      'address_line2' => rand(0, 1) ? 'Suite 100' : NULL,
      'postal_code' => '68102',
    ]);
  }
  $service_location_address->save();

  return $service_location_address;
}

/**
 * Create service location hours.
 *
 * @param string $hours
 *   The type of hours for a facility.
 */
function create_service_location_hours($hours) {
  $office_hours_sets = [
    // Open 8:00 AM - 6:31 PM Monday - Friday, closed on weekends.
    'hours_mf8_631' => [
      [
        // Sunday.
        'day' => 0,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
        // Monday.
        'day' => 1,
        'starthours' => 800,
        'endhours' => 1831,
        'comment' => '',
      ],
      [
        // Tuesday.
        'day' => 2,
        'starthours' => 800,
        'endhours' => 1831,
        'comment' => '',
      ],
      [
         // Wednesday.
        'day' => 3,
        'starthours' => 800,
        'endhours' => 1831,
        'comment' => '',
      ],
      [
         // Thursday.
        'day' => 4,
        'starthours' => 800,
        'endhours' => 1831,
        'comment' => '',
      ],
      [
        // Friday.
        'day' => 5,
        'starthours' => 800,
        'endhours' => 1831,
        'comment' => '',
      ],
      [
        // Saturday.
        'day' => 6,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
    ],
    // Open 24/7, per the comments.
    'hours_24_7' => [
      [
        // Sunday.
        'day' => 0,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
      [
        // Monday.
        'day' => 1,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
      [
        // Tuesday.
        'day' => 2,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
      [
        // Wednesday.
        'day' => 3,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
      [
        // Thursday.
        'day' => 4,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
      [
        // Friday.
        'day' => 5,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
      [
        // Saturday.
        'day' => 6,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => '24/7',
      ],
    ],
    // Open 12:00 AM - 12:00 AM every day.
    'hours_12a_12a' => [
      [
        // Sunday.
        'day' => 0,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
      [
        // Monday.
        'day' => 1,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
      [
        // Tuesday.
        'day' => 2,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
      [
        // Wednesday.
        'day' => 3,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
      [
        // Thursday.
        'day' => 4,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
      [
        // Friday.
        'day' => 5,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
      [
        // Saturday.
        'day' => 6,
        'starthours' => 0000,
        'endhours' => 0000,
        'comment' => '',
      ],
    ],
    // Closed every day.
    'hours_closed' => [
      [
        // Sunday.
        'day' => 0,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
         // Monday.
        'day' => 1,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
        // Tuesday.
        'day' => 2,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
        // Wednesday.
        'day' => 3,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
        // Thursday.
        'day' => 4,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
        // Friday.
        'day' => 5,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
      [
        // Saturday.
        'day' => 6,
        'starthours' => NULL,
        'endhours' => NULL,
        'comment' => 'Closed',
      ],
    ],
  ];
  $service_location_hours = $office_hours_sets[$hours];

  return $service_location_hours;
}

/**
 * Create email contacts for a service location.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $service_location
 *   The service location paragraph.
 * @param string $paragraph_field
 *   The paragraph field to which the email contacts will be appended.
 * @param int $number_of_email_addresses
 *   The number of email addresses to create.
 */
function create_email_contacts($service_location, $paragraph_field, $number_of_email_addresses) {
  for ($i = 0; $i < $number_of_email_addresses; $i++) {
    $service_location->get($paragraph_field)->appendItem(create_email_paragraph($i));
  }
}

/**
 * Create an email paragraph.
 *
 * @param int $email_index
 *   The index of the loop to create multiple email addresses.
 *
 * @return \Drupal\paragraphs\Entity\Paragraph
 *   The email paragraph.
 */
function create_email_paragraph($email_index) {
  $email_address = 'service_location_contact' . $email_index + 1 . '@example.com';
  $email = Paragraph::create([
    'type' => 'email_contact',
    'field_email_address' => $email_address,
    'field_email_label' => 'Email ' . $email_index + 1,
  ]);
  return $email;
}

/**
 * Randomly choose a true, false, or null value.
 *
 * @return string
 *   The true, false, or null value.
 */
function choose_random_true_false_null() {
  $true_false_null = [
    '1',
    '0',
    NULL,
  ];
  $random_true_false_null = $true_false_null[array_rand($true_false_null)];
  return $random_true_false_null;

}

/**
 * Randomly choose whether online scheduling is available.
 *
 * @return string
 *   The online scheduling availability choice.
 */
function choose_random_online_scheduling_avail_option() {
  $online_scheduling_avail_options = [
    'yes',
    'no',
    NULL,
  ];
  $random_online_scheduling_avail_option = $online_scheduling_avail_options[array_rand($online_scheduling_avail_options)];
  return $random_online_scheduling_avail_option;
}

/**
 * Randomly choose an office visit option.
 *
 * @return string
 *   The office visit option.
 */
function choose_random_office_visit() {
  $office_visits_options = [
    'no',
    'yes_appointment_only',
    'yes_walk_in_visits_only',
    'yes_with_or_without_appointment',
    NULL,
  ];
  $random_office_visit = $office_visits_options[array_rand($office_visits_options)];
  return $random_office_visit;
}

/**
 * Randomly choose a virtual support option.
 *
 * @return string
 *   The virtual support option.
 */
function choose_random_virtual_support() {
  $virtual_support_options = [
    'no',
    'yes_appointment_only',
    'yes_veterans_can_call',
    'virtual_visits_may_be_available',
    NULL,
  ];
  $random_virtual_support = $virtual_support_options[array_rand($virtual_support_options)];
  return $random_virtual_support;
}

/**
 * Randomly choose an introduction text type.
 *
 * @return string
 *   The appointment introduction text type.
 */
function choose_random_appt_intro_text_type() {
  $appt_intro_text_type_options = [
    'use_default_text',
    'customize_text',
    'remove_text',
    NULL,
  ];
  $random_appt_intro_text_type = $appt_intro_text_type_options[array_rand($appt_intro_text_type_options)];
  return $random_appt_intro_text_type;

}

/**
 * Randomly choose a phone number type.
 *
 * @return string
 *   The phone number type.
 */
function choose_random_phone_number_type() {
  $phone_number_types = [
    'tel',
    'fax',
    'sms',
    'tty',
  ];
  $random_phone_number_type = $phone_number_types[array_rand($phone_number_types)];
  return $random_phone_number_type;
}

/**
 * Generate a random phone number.
 *
 * @return string
 *   A phone number.
 */
function generate_random_phone_number() {
  $area_code = rand(200, 989);
  $prefix = rand(100, 999);
  $line_number = rand(1000, 9999);
  $phone_number = sprintf("%03d-%03d-%04d", $area_code, $prefix, $line_number);
  return $phone_number;
}

/**
 * Add prepare for your visit to facility.
 *
 * @param \Drupal\node\Entity\Node $node
 *   The facility node.
 * @param int $number_of_accordions
 *   The number of accordions to add.
 */
function add_prepare_for_your_visit_to_facility($node, $number_of_accordions) {
  for ($i = 0; $i < $number_of_accordions; $i++) {
    $prepare_for_your_visit_new = Paragraph::create([
      'type' => 'basic_accordion',
      'field_header' => 'Prepare for your visit ' . $i + 1,

      'field_rich_wysiwyg' => [
        'value' => 'Prepare for your visit body ' . $i + 1,
        'format' => 'rich_text_limited',
      ],
    ]);
    $prepare_for_your_visit_new->save();
    $node->field_prepare_for_visit->appendItem($prepare_for_your_visit_new);
  }
  $node->save();
}

/**
 * Add media to facility.
 *
 * @param \Drupal\node\Entity\Node $node
 *   The facility node.
 * @param int $media_id
 *   The media node to add.
 */
function add_media_to_facility($node, $media_id) {
  // Load the media entity.
  $media = Media::load($media_id);
  // Check if the media entity exists and is an image.
  if ($media && $media->bundle() == 'image') {
    // Append the media entity to the field.
    $media = \Drupal::entityTypeManager()->getStorage('media')->load($media_id);
    $node->field_media->appendItem($media);
    $node->save();
  }
}

/**
 * Add spotlights to facility.
 *
 * @param \Drupal\node\Entity\Node $node
 *   The facility node.
 * @param int $number_of_spotlights
 *   The number of spotlights to add.
 * @param bool $use_cta
 *   Whether to add a CTA to the spotlight.
 */
function add_spotlights_to_facility($node, $number_of_spotlights, $use_cta) {
  for ($i = 0; $i < $number_of_spotlights; $i++) {
    if ($use_cta) {
      $cta = Paragraph::create([
        'type' => 'button',
        'field_button_link' => 'https://www.google.com',
        'field_button_label' => 'Spotlight CTA ' . $i + 1,
      ]);
      $cta->save();
    }
    else {
      $cta = NULL;
    }
    $spotlight = Paragraph::create([
      'type' => 'featured_content',
      'field_section_header' => 'Spotlight title ' . $i + 1,
      'field_description' => 'Spotlight body ' . $i + 1,
    ]);

    $spotlight->field_cta->appendItem($cta);
    $spotlight->save();
    $node->field_local_spotlight->appendItem($spotlight);
    $node->save();
  }
}
