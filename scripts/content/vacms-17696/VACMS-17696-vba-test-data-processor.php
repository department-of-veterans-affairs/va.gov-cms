<?php

/**
 * @file
 * Creates VBA test data.
 */

use Drupal\node\Entity\Node;
use Psr\Log\LogLevel;

require_once __DIR__ . '../../script-library.php';

function create_vba_facility_service_node($data) {
  $facility_id = $data[0];
  $facility_section = $data[1];
  $service_id = $data[2];

  // Create the node.
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
    // Add any other fields you need for your node.
  ]);

  // $service_location_paragraphs = createServiceLocationParagraphs($data);

  $service_node->save();
}

function create_service_location_paragraphs($data) {

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
