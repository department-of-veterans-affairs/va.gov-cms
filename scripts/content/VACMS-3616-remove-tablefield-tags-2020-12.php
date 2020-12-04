<?php

/**
 * @file
 * Remove unwanted html from plain text table cells.
 *
 *  VACMS-3616-remove-tablefield-tags-2020-12.php.
 */

use Psr\Log\LogLevel;

// Create database connection.
$database = \Drupal::database();

// Processing of fields.
if (isset($database)) {
  print("SUCCESSFULLY connected to drupal database. Beginning process...\n\n");
  va_gov_table_field_tag_removal($database);
}
else {
  print("FAILURE to connect to drupal database\n\n");
}

/**
 * Strip unwanted tags from table fields in db.
 *
 * @param string $database
 *   Database connection.
 */
function va_gov_table_field_tag_removal(&$database) {
  $fields = [
    'paragraph__field_facility_service_hours',
    'paragraph_revision__field_facility_service_hours',
    'node__field_facility_hours',
    'node_revision__field_facility_hours',
  ];
  foreach ($fields as $field) {
    $bundle = 'health_care_local_facility';
    $value_column = 'field_facility_hours_value';
    if ($field === 'paragraph_revision__field_facility_service_hours' || $field === 'paragraph__field_facility_service_hours') {
      $bundle = 'service_location';
      $value_column = 'field_facility_service_hours_value';
    }
    // Build the select query.
    $hours_result = $database->select($field, 'r');
    $hours_result->fields('r', ['revision_id', $value_column])
      ->condition('r.bundle', $bundle);
    $record = $hours_result->execute();
    $hours_result = $record->fetchAll();

    $revisions['total'] = count($hours_result);
    $revisions['processed'] = 0;

    print("There are {$revisions['total']} {$field} to be processed.\n");

    // Log the number of revisions to be processed.
    Drupal::logger('va_gov_db')
      ->log(LogLevel::INFO, 'Number of revisions with :field to be processed: %count .', [
        ':field' => $field,
        '%count' => $revisions['total'],
      ]);
    foreach ($hours_result as $hour_result) {
      $stripped_tags_raw = unserialize($hour_result->$value_column);
      array_walk_recursive($stripped_tags_raw, 'str_replace_unserialized_data');
      $stripped_tags_serialized = serialize($stripped_tags_raw);
      $query = $database->update($field)
        ->fields([
          $value_column => $stripped_tags_serialized,
        ])
        ->condition('bundle', $bundle)
        ->condition('revision_id', $hour_result->revision_id);
       $updated_count = $query->execute();
      if (!empty($updated_count)){
        $revisions['processed'] = $revisions['processed'] + $updated_count;
      }
    }

    // Log the number of updates.
    Drupal::logger('va_gov_db')
      ->log(LogLevel::INFO, 'Successfully processed %show_count of %count :field updates.', [
        ':field' => $field,
        '%count' => $revisions['total'],
        '%show_count' => $revisions['processed'],
      ]);

    print(">>> Successfully processed {$revisions['processed']} of {$revisions['total']} {$field} updates.\n");
  }

}

/**
 * Strip unwanted tags from table fields in db.
 *
 * @param string $value
 *   The string to cleanup.
 */
function str_replace_unserialized_data(&$value) {
  $strings_to_remove = [
    '<p>',
    '</p>',
    'p&amp;',
    '/p&amp;',
    '&amp;lt;',
    '&amp;gt;',
    '&amp;',
    'amp;',
    'gt;',
    'lt;',
    '&gt;',
    '&lt;/p&gt;',
    '&lt;p&gt;',
    '&p&',
    '&/p&',
  ];
  $value = str_replace($strings_to_remove, '', $value);
}
