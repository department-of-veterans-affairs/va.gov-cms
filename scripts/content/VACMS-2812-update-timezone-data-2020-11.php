<?php

/**
 * @file
 * Migrate all fields_date to field_datetime_range_timezone.
 *
 *  VACMS-2812-update-timezone-data-2020-11.php.
 */

use Psr\Log\LogLevel;
use Drupal\Core\Database\Database;

// Create database connection.
$database = \Drupal::database();

// Processing of field date migrations.
if (isset($database)) {
  print("SUCCESSFULLY connected to drupal database. Beginning migration...\n\n");
  va_gov_event_field_date_migration($database);
  va_gov_situation_update_date_revisions_migration($database);
}else {
  print("FAILURE to connect to drupal database\n\n");
}

/**
 * Migration of Event field_date to field_datetime_range_timezone.
 *
 * @param string $database
 *  Database connection.
 *
 */
function va_gov_event_field_date_migration(&$database) {

  // Clear node field_datetime_range_timezone revision table.
  $event_date_revision_clear = $database->delete('node_revision__field_datetime_range_timezone');
  $event_date_revision_clear->execute();

  // Clear node field_datetime_range_timezone table.
  $event_date_clear = $database->delete('node__field_datetime_range_timezone');
  $event_date_clear->execute();

  // Build count of node revision field date.
  $revision_query = $database->select('node_revision__field_date', 'n');
  $revision_query->addField('n','bundle');
  $revision_query->condition('bundle', 'event');
  $count_query = $revision_query->execute()->fetchAll();
  $revisions['total'] = count($count_query);

  // Build count of node field date.
  $node_query = $database->select('node__field_date', 'n');
  $node_query->addField('n','bundle');
  $node_query->condition('bundle', 'event');
  $node_count_query = $node_query->execute()->fetchAll();
  $node['node_total'] = count($node_count_query);

  print("There are {$revisions['total']} Event revisions with field date to be processed.\n");
  print("There are {$node['node_total']} Event nodes with field date to be processed.\n\n");

  // Log the number of revisions to be processed.
  Drupal::logger('va_gov_db')
  ->log(LogLevel::INFO, 'Number of revisions with field date to be processed: %count .', [
    '%count' => $revisions['total'],
  ]);

  // Log the number of nodes to be processed.
  Drupal::logger('va_gov_db')
  ->log(LogLevel::INFO, 'Number of nodes with field date to be processed: %count .', [
    '%count' => $node['node_total'],
  ]);
  
  // Build the select query.
  $rev_result = $database->select('node_revision__field_date', 'r');
  $rev_result->fields('r', ['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_date_value', 'field_date_end_value']);
  $rev_result->addExpression("TIMESTAMPDIFF(MINUTE, field_date_value, field_date_end_value)", 'field_datetime_range');
  $rev_result->addExpression("UNIX_TIMESTAMP(field_date_value)", 'field_date_value');
  $rev_result->addExpression("UNIX_TIMESTAMP(field_date_end_value)", 'field_date_end_value');
  $record = $rev_result->execute();
  $rev_record = $record->fetchAll();
  $rev_record_array = _objToArray($rev_record);

  // Build the insert query.
  $rev_insert = $database->insert('node_revision__field_datetime_range_timezone')->fields(['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_datetime_range_timezone_value', 'field_datetime_range_timezone_end_value', 'field_datetime_range_timezone_duration', 'field_datetime_range_timezone_timezone']);

  foreach ($rev_record_array as $rev) {
    $rev_insert->values(
      ([
      'bundle' => $rev['bundle'],
      'deleted' => $rev['deleted'],
      'entity_id' => $rev['entity_id'],
      'revision_id' => $rev['revision_id'],
      'langcode' => $rev['langcode'],
      'delta' => $rev['delta'],
      'field_datetime_range_timezone_value' => $rev['field_date_value'],
      'field_datetime_range_timezone_end_value' => $rev['field_date_end_value'],
      'field_datetime_range_timezone_duration' => $rev['field_datetime_range'],
      'field_datetime_range_timezone_timezone' => ""
      ])
    );
  }

  $show_count = count($rev_record_array);
  // Do not continue if no results.
  if ($show_count == 0) {
    print("No event date revisions were found to be processed.\n");
  }else {
    $rev_insert->execute();

    // Log the number of updates.
    Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Successfully processed %show_count of %count Event date revision updates.', [
      '%count' => $revisions['total'],
      '%show_count' => $show_count,
    ]);

    print(">>> Successfully processed {$show_count} of {$revisions['total']} Event date revision updates.\n");
  }

  // Build the select query.
  $node_result = $database->select('node__field_date', 'n');
  $node_result->fields('n', ['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_date_value', 'field_date_end_value']);
  $node_result->addExpression("TIMESTAMPDIFF(MINUTE, field_date_value, field_date_end_value)", 'field_datetime_range');
  $node_result->addExpression("UNIX_TIMESTAMP(field_date_value)", 'field_date_value');
  $node_result->addExpression("UNIX_TIMESTAMP(field_date_end_value)", 'field_date_end_value');
  $node_record = $node_result->execute();
  $node_records = $node_record->fetchAll();
  $node_records_array = _objToArray($node_records);

  // Build the insert query.
  $node_insert = $database->insert('node__field_datetime_range_timezone')->fields(['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_datetime_range_timezone_value', 'field_datetime_range_timezone_end_value', 'field_datetime_range_timezone_duration', 'field_datetime_range_timezone_timezone']);

  foreach ($node_records_array as $node_update) {
    $node_insert->values(
      ([
      'bundle' => $node_update['bundle'],
      'deleted' => $node_update['deleted'],
      'entity_id' => $node_update['entity_id'],
      'revision_id' => $node_update['revision_id'],
      'langcode' => $node_update['langcode'],
      'delta' => $node_update['delta'],
      'field_datetime_range_timezone_value' => $node_update['field_date_value'],
      'field_datetime_range_timezone_end_value' => $node_update['field_date_end_value'],
      'field_datetime_range_timezone_duration' => $node_update['field_datetime_range'],
      'field_datetime_range_timezone_timezone' => ""
      ])
    );
  }

  $node_show_count = count($node_records_array);
  // Do not continue if no results.
  if ($node_show_count == 0) {
    print("No event date revisions were found to be processed.\n");
  }else {
    $node_insert->execute();

    // Log the number of updates..
    Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Successfully processed %show_count of %count node event date updates.', [
      '%count' => $node['node_total'],
      '%show_count' => $node_show_count,
    ]);

    print(">>> Successfully processed {$node_show_count} of {$node['node_total']} node event date updates.\n\n");
  }
}

/**
 * Migration of Situation Update field_date to field_datetime_range_timezone.
 *
 * @param string $database
 *  Database connection.
 *
 */
function va_gov_situation_update_date_revisions_migration(&$database) {

  // Clear paragraph field_datetime_range_timezone revision table.
  $situation_update_date_revision_clear = $database->delete('paragraph_r__d38323513b');
  $situation_update_date_revision_clear->execute();
  
  // Clear paragraph field_datetime_range_timezone revision table.
  $situation_update_date_clear = $database->delete('paragraph__field_datetime_range_timezone');
  $situation_update_date_clear->execute();

  // Build count of revisions.
  $situation_update_revision_query = $database->select('paragraph_revision__field_date_and_time', 'p');
  $situation_update_revision_query->addField('p','bundle');
  $situation_update_revision_query->condition('bundle', 'situation_update');
  $situation_update_count_query = $situation_update_revision_query->execute()->fetchAll();
  $situation_updates_revisions['situation_update_total'] = count($situation_update_count_query);

  // Build count of paragraphs.
  $situation_update_paragraph_query = $database->select('paragraph__field_date_and_time', 'p');
  $situation_update_paragraph_query->addField('p','bundle');
  $situation_update_paragraph_query->condition('bundle', 'situation_update');
  $situation_update_paragraph_count_query = $situation_update_paragraph_query->execute()->fetchAll();
  $situation_updates_paragraph_count['situation_update_total'] = count($situation_update_paragraph_count_query);

  print("There are {$situation_updates_revisions['situation_update_total']} Situation Update field date revisions to be processed.\n");
  print("There are {$situation_updates_paragraph_count['situation_update_total']} Situation Update field date paragraphs to be processed.\n\n");

  // Log amount to be processed.
  Drupal::logger('va_gov_db')
  ->log(LogLevel::INFO, 'Number of Situation Update field date revisions to be processed: %count .', [
    '%count' => $situation_updates_revisions['situation_update_total'],
  ]);

  // Log amount to processed.
  Drupal::logger('va_gov_db')
  ->log(LogLevel::INFO, 'Number of Situation Update field date paragraphs to be processed: %count .', [
    '%count' => $situation_updates_paragraph_count['situation_update_total'],
  ]);

  // Build the select query.
  $situation_update_rev_result = $database->select('paragraph_revision__field_date_and_time', 'r');
  $situation_update_rev_result->fields('r', ['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_date_and_time_value', 'field_date_and_time_value']);
  $situation_update_rev_result->addExpression("UNIX_TIMESTAMP(field_date_and_time_value)", 'field_date_and_time_value');
  $situation_update_rev_result->addExpression("UNIX_TIMESTAMP(field_date_and_time_value)", 'field_date_and_time_value');
  $situation_update_record = $situation_update_rev_result->execute();
  $situation_update_rev_record = $situation_update_record->fetchAll();
  $situation_update_rev_record_array = _objToArray($situation_update_rev_record);

  // Build the insert query.
  $situation_update_rev_insert = $database->insert('paragraph_r__d38323513b')->fields(['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_datetime_range_timezone_value', 'field_datetime_range_timezone_end_value', 'field_datetime_range_timezone_timezone']);

  foreach ($situation_update_rev_record_array as $situation_update_rev) {
    $situation_update_rev_insert->values(
      ([
      'bundle' => $situation_update_rev['bundle'],
      'deleted' => $situation_update_rev['deleted'],
      'entity_id' => $situation_update_rev['entity_id'],
      'revision_id' => $situation_update_rev['revision_id'],
      'langcode' => $situation_update_rev['langcode'],
      'delta' => $situation_update_rev['delta'],
      'field_datetime_range_timezone_value' => $situation_update_rev['field_date_and_time_value'],
      'field_datetime_range_timezone_end_value' => $situation_update_rev['field_date_and_time_value'],
      'field_datetime_range_timezone_timezone' => ""
      ])
    );
  }

  $situation_update_show_count = count($situation_update_rev_record_array);
  // Do not continue if no results.
  if ($situation_update_show_count == 0) {
    $situation_updates_revisions['#finished'] = 1;
    return "No Situation Update date revisions were found to be processed.\n";
  }else {
    $situation_update_rev_insert->execute();
    $situation_updates_revisions['#finished'] = 1;

    // Log the number of updates.
    Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Successfully processed %show_count of %count Situation Update field date revision updates.', [
      '%count' => $situation_updates_revisions['situation_update_total'],
      '%show_count' => $situation_update_show_count,
    ]);

    print(">>> Successfully processed {$situation_update_show_count} of {$situation_updates_revisions['situation_update_total']} Situation Update field date revision updates.\n");
  }

  // Build the select query.
  $paragraph_result = $database->select('paragraph__field_date_and_time', 'n');
  $paragraph_result->fields('n', ['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_date_and_time_value', 'field_date_and_time_value']);
  $paragraph_result->addExpression("UNIX_TIMESTAMP(field_date_and_time_value)", 'field_date_and_time_value');
  $paragraph_result->addExpression("UNIX_TIMESTAMP(field_date_and_time_value)", 'field_date_and_time_value');
  $paragraph_record = $paragraph_result->execute();
  $paragraph_records = $paragraph_record->fetchAll();
  $paragraph_records_array = _objToArray($paragraph_records);

  // Build the insert query.
  $paragraph_insert = $database->insert('paragraph__field_datetime_range_timezone')->fields(['bundle', 'deleted', 'entity_id', 'revision_id', 'langcode', 'delta', 'field_datetime_range_timezone_value', 'field_datetime_range_timezone_end_value', 'field_datetime_range_timezone_timezone']);

  foreach ($paragraph_records_array as $paragraph_update) {
    $paragraph_insert->values(
      ([
      'bundle' => $paragraph_update['bundle'],
      'deleted' => $paragraph_update['deleted'],
      'entity_id' => $paragraph_update['entity_id'],
      'revision_id' => $paragraph_update['revision_id'],
      'langcode' => $paragraph_update['langcode'],
      'delta' => $paragraph_update['delta'],
      'field_datetime_range_timezone_value' => $paragraph_update['field_date_and_time_value'],
      'field_datetime_range_timezone_end_value' => $paragraph_update['field_date_and_time_value'],
      'field_datetime_range_timezone_timezone' => ""
      ])
    );
  }

  $paragraph_show_count = count($paragraph_records_array);
  // Do not continue if no results.
  if ($paragraph_show_count == 0) {
    print("No event date revisions were found to be processed.\n");
  }else {
    $paragraph_insert->execute();

    // Log the number updates.
    Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Successfully processed %show_count of %count Situation Updates paragraphs.', [
      '%count' => $situation_updates_paragraph_count['situation_update_total'],
      '%show_count' => $paragraph_show_count,
    ]);

    print(">>> Successfully processed {$paragraph_show_count} of {$situation_updates_paragraph_count['situation_update_total']} Paragraph Situation Update field date updates.\n\n");
  }
}

/**
 * Converts object returned from fetchAll to Array.
 *
 * @param object $obj
 *  Array object to be processed.
 *
 */
function _objToArray($obj) {
  if(!is_array($obj) && !is_object($obj)) 
      return $obj;
  if(is_object($obj)) 
      $obj = get_object_vars($obj);
  return 
      array_map(__FUNCTION__, $obj);
}
