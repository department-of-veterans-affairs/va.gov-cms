<?php

/**
 * @file
 * Creates VBA test data.
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Psr\Log\LogLevel;

require_once __DIR__ . '../../script-library.php';

/**
 * Randomly choose a true, false, or null value.
 *
 * @return string
 *  The true, false, or null value.

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
 * The online scheduling availability choice.
 */
function choose_random_online_scheduling_avail_option() {
  $online_scheduling_avail_options = [
    'yes',
    'no',
    null,
  ];
  $random_online_scheduling_avail_option = $online_scheduling_avail_options[array_rand($online_scheduling_avail_options)];
  return $random_online_scheduling_avail_option;
}

/**
 * Randomly choose an office visit option.
 *
 * @return string
 * The office visit option.
 */
function choose_random_office_visit() {
  $office_visits_options = [
    'no',
    'yes_appointment_only',
    'yes_walk_in_visits_only',
    'yes_with_or_without_appointment',
    null,
  ];
  $random_office_visit = $office_visits_options[array_rand($office_visits_options)];
  return $random_office_visit;
}

/**
 * Randomly choose a virtual support option.
 *
 * @return string
 * The virtual support option.
 */
function choose_random_virtual_support() {
  $virtual_support_options = [
    'no',
    'yes_appointment_only',
    'yes_veterans_can_call',
    'virtual_visits_may_be_available',
    null,
  ];
  $random_virtual_support = $virtual_support_options[array_rand($virtual_support_options)];
  return $random_virtual_support;
}

/**
 * Randomly choose an introduction text type.
 *
 * @return string
 *  The appointment introduction text type.
 */
function choose_random_appt_intro_text_type() {
  $appt_intro_text_type_options = [
    'use_default_text',
    'customize_text',
    'remove_text',
    null,
  ];
  $random_appt_intro_text_type = $appt_intro_text_type_options[array_rand($appt_intro_text_type_options)];
  return $random_appt_intro_text_type;

}

/**
 * Randomly choose a phone number type.
 *
 * @return string
 * The phone number type.
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
 * A phone number.
 */
function generate_random_phone_number() {
  $area_code = rand(200, 989);
  $prefix = rand(100, 999);
  $line_number = rand(1000, 9999);
  $phone_number = sprintf("%03d-%03d-%04d", $area_code, $prefix, $line_number);
  return $phone_number;
}

/**
 * Create phone numbers for a service location.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $service_location
 * The service location paragraph.
 * @param string $paragraph_field
 * The paragraph field to which the phone numbers will be appended.
 * @param int $number_of_phone_numbers
 * The number of phone numbers to create.
 * @param string $phone_type
 * The type of phone number. (e.g., 'tel', 'fax', 'sms', 'tty')
 * @param int $create_extension
 * Whether to create an extension for the first phone number.
 */
function create_service_location_phone_numbers($service_location, $paragraph_field, $number_of_phone_numbers, $phone_type, $create_extension) {
  // There can be multiple appointment phone numbers for a service location.
  // We want to ensure that the first appointment phone number is of the type
  // and has an extension, as specified in the CSV file. The rest of the appointment
  // phone numbers can be of a random type with random extensions or no extension.
  $phone_numbers = NULL;
  for ($i = 0; $i < $number_of_phone_numbers; $i++) {
    if ($i === 0) {
      $service_location->get($paragraph_field)->appendItem(create_phone_paragraph($i, $phone_type, (bool)$create_extension));
    }
    else {
      $service_location->get($paragraph_field)->appendItem(create_phone_paragraph($i, choose_random_phone_number_type(), (bool)rand(0, 1)));
    }
  }

  return $phone_numbers;
}

/**
 * Create a service location appointment phone paragraph.
 *
 * @param int $phone_index
 * The index of the loop to create multiple phone numbers.
 * @param string $phone_type
 * The type of phone number. (e.g., 'tel', 'fax', 'sms', 'tty')
 * @param bool $add_extension
 * Whether to add an extension to the phone number.
 */
function create_phone_paragraph($phone_index, $phone_type, $add_extension = NULL) {
  $phone_number = generate_random_phone_number();

  // We only want an extension when the phone type is 'tel'.
  if ($phone_type === 'tel') {
    $random_extension = (string)rand(1, 99999);
    $add_extension = (is_null($add_extension)) ? rand(0, 1) : $add_extension;
  } else {
    $random_extension = NULL;
    $add_extension = NULL;
  }
  $extension = $add_extension ? $random_extension : NULL;

  $phone = Paragraph::create([
    'type' => 'phone_number',
    'field_phone_number' => $phone_number,
    'field_phone_number_type' => $phone_type,
    'field_phone_extension' => $extension,
    'field_phone_label' => 'Appointment Phone ' . $phone_index + 1,
  ]);
  $phone->save();
  return $phone;
}

/**
 * Create service location paragraphs.
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

  } else {
      // Otherwise, generate random data.
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
  if ($data[20] === '2') {
    $service_location->field_office_hours = create_service_location_hours($data[20], $data[21], $data[22]);
  }
  create_service_location_phone_numbers($service_location, 'field_phone', $number_of_contact_phones, $data[25], $data[26]);
  create_email_contacts($service_location, 'field_email_contacts', $number_of_contact_emails);
  $service_location->save();



  // $service_location_email = create_service_location_email($data);
  // $service_location->field_service_location_email->appendChild($service_location_email);

  return $service_location;
}

function create_vba_facility_service_node($data) {
  $facility_id = $data[1];
  $facility_section = $data[3];
  $service_id = $data[5];

  // Create the VBA service node.
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

  // $service_location_paragraphs = createServiceLocationParagraphs($data);

  $service_node->save();

  $node_id = $service_node->id();

  $number_of_service_locations = $data[6];

  for ($i = 0; $i < $number_of_service_locations; $i++) {
    $service_location = create_service_location_paragraph($data, $i);
    $service_node->field_service_location->appendItem($service_location);
  }
  $service_node->save();
}

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
      'address_line2' => rand(0,1) ? 'Suite 100' : NULL,
      'postal_code' => '68102',
    ]);
  }
  $service_location_address->save();

  return $service_location_address;
}

function create_service_location_hours($use_facility_hours, $hours, $additional_hours_info) {
$office_hours_sets = [
  'hours_mf8_4' => [
    [
      'day' => 0, // Sunday
      'starthours' => NULL, // 9:00 AM
      'endhours' => NULL, // 5:00 PM
      'comment' => 'Closed',
    ],
    [
      'day' => 1, // Monday
      'starthours' => 800, // 8:00 AM
      'endhours' => 1600, // 4:00 PM
      'comment' => '',
    ],
    [
      'day' => 2, // Tuesday
      'starthours' => 800, // 8:00 AM
      'endhours' => 1600, // 4:00 PM
      'comment' => '',
    ],
    [
      'day' => 3, // Wednesday
      'starthours' => 800, // 8:00 AM
      'endhours' => 1600, // 4:00 PM
      'comment' => '',
    ],
    [
      'day' => 4, // Thursday
      'starthours' => 800, // 8:00 AM
      'endhours' => 1600, // 4:00 PM
      'comment' => '',
    ],
    [
      'day' => 5, // Friday
      'starthours' => 800, // 8:00 AM
      'endhours' => 1600, // 4:00 PM
      'comment' => '',
    ],
    [
      'day' => 6, // Saturday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
  ],
  'hours_24_7' => [
    [
      'day' => 0, // Sunday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
    [
      'day' => 1, // Monday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
    [
      'day' => 2, // Tuesday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
    [
      'day' => 3, // Wednesday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
    [
      'day' => 4, // Thursday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
    [
      'day' => 5, // Friday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
    [
      'day' => 6, // Saturday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => '24/7',
    ],
  ],
  'hours_12a_12a' => [
    [
      'day' => 0, // Sunday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
    [
      'day' => 1, // Monday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
    [
      'day' => 2, // Tuesday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
    [
      'day' => 3, // Wednesday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
    [
      'day' => 4, // Thursday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
    [
      'day' => 5, // Friday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
    [
      'day' => 6, // Saturday
      'starthours' => 0, // 12:00 AM
      'endhours' => 2400, // 12:00 AM
      'comment' => '',
    ],
  ],
  'hours_closed' => [
    [
      'day' => 0, // Sunday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
    [
      'day' => 1, // Monday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
    [
      'day' => 2, // Tuesday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
    [
      'day' => 3, // Wednesday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
    [
      'day' => 4, // Thursday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
    [
      'day' => 5, // Friday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
    [
      'day' => 6, // Saturday
      'starthours' => NULL,
      'endhours' => NULL,
      'comment' => 'Closed',
    ],
  ],
];
$service_location_hours = $office_hours_sets[$hours];

return $service_location_hours;
}

function create_email_contacts($service_location, $paragraph_field, $number_of_email_addresses) {
  for ($i = 0; $i < $number_of_email_addresses; $i++) {
    $service_location->get($paragraph_field)->appendItem(create_email_paragraph($i));
  }
}

function create_email_paragraph($email_index) {
  $email_address = 'service_location_contact' . $email_index + 1 . '@example.com';
  $email = Paragraph::create([
    'type' => 'email_contact',
    'field_email_address' => $email_address,
    'field_email_label' => 'Email ' . $email_index + 1,
  ]);
  return $email;
}

// Specify the path to the CSV file.
$csv_file_path = __DIR__ . '/VACMS-17696-vba-test-data-source.csv';

// Open the CSV file.
if (($handle = fopen($csv_file_path, 'r')) !== FALSE) {
  // Read and discard the header row.
  fgetcsv($handle);

  // Read the rest of the CSV file.
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    // Each row in the CSV file becomes an array in $data.
    // The array and CSV are in the same order.

    create_vba_facility_service_node($data);

  }

  // Close the CSV file.
  fclose($handle);
}
