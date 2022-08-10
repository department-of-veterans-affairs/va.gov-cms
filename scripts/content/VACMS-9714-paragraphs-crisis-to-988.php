<?php

/**
 * @file
 * Replace 800-273-8255 with 988 for paragraphs.
 *
 * VACMS-9714-paragraphs-crisis-to-988.php.
 */

require_once __DIR__ . '/script-library.php';

use Psr\Log\LogLevel;

$revision_message = 'Updated Crisis number from 800-273-8255 to 988';
$sandbox = ['#finished' => 0];
do {
  print(va_gov_change_crisis_hotline_to_988_nodes($sandbox, $revision_message));
} while ($sandbox['#finished'] < 1);
// Paragraph processing complete.  Call this done.
return;

/**
 * Replace 800-273-8255 with 988.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 * @param string $revision_message
 *   Text to be used in revision log message.
 *
 * @return string
 *   Status message.
 */
function va_gov_change_crisis_hotline_to_988_nodes(array &$sandbox, $revision_message) {
  $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');

  // Get the count for paragraphs.
  // This runs only once.
  if (!isset($sandbox['total'])) {
    $paragraph_types = [
      'collapsible_panel_item',
      'health_care_local_facility_servi',
      'wysiwyg',
    ];

    $query = $paragraph_storage->getQuery();
    $query->condition('type', $paragraph_types, 'IN');
    $pids_to_update = $query->execute();
    $result_count = count($pids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    $sandbox['updated'] = 0;

    // Create non-numeric keys to accurately remove each nid when processed.
    $sandbox['pids_to_update'] = array_combine(
      array_map('_va_gov_stringifypid', array_values($pids_to_update)),
      array_values($pids_to_update)
    );
  }

  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No paragraphs found for processing.\n";
  }

  $limit = 25;

  // Load entities.
  $paragraph_ids = array_slice($sandbox['pids_to_update'], 0, $limit, TRUE);
  $paragraphs = $paragraph_storage->loadMultiple($paragraph_ids);
  foreach ($paragraphs as $paragraph) {
    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $old_value = $paragraph->get('field_wysiwyg')->value;
    $new_value = normalize_crisis_number($old_value);

    // If the value was altered then update the node.
    if ($new_value !== $old_value) {
      $paragraph->field_wysiwyg->setValue(
        [
          'value' => $new_value,
          'format' => 'rich_text',
        ]
      );
      $paragraph->save();
      $sandbox['updated']++;
      $pids[] = $paragraph->id();
    }

    unset($sandbox['pids_to_update']["paragraph_{$paragraph->id()}"]);
    $sandbox['current'] = $sandbox['total'] - count($sandbox['pids_to_update']);
  }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, '%current paragraphs progress. Paragraphs updated: %pids', [
      '%current' => $sandbox['current'],
      '%pids' => implode(', ', $pids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'Processing all %count paragraphs completed', [
      '%count' => $sandbox['total'],
    ]);
    return "Paragraph updates complete. {$sandbox['current']} / {$sandbox['total']} - Total updated: {$sandbox['updated']}\n";
  }

  return "Processed paragraphs... {$sandbox['current']} / {$sandbox['total']}.\n";
}

/**
 * Normalize all crisis hotline instances in a provided string.
 *
 * @param string $input
 *   The string to normalize.
 * @param bool $plain
 *   True if the result should be a plain string, false for html.
 *
 * @return string
 *   The value of $input with all crisis numbers updated.
 */
function normalize_crisis_number($input, $plain = FALSE): string {
  $replacement_string = '988';
  $replacement_html = '<a aria-label="9 8 8" href="tel:988">988</a>';
  // Remove telephone "link" from number.
  $first_pattern = "/\<a[^>]*\>(?:1-)?800[\-\.]273[\-\.]8255\<\/a\>/i";
  $output = preg_replace($first_pattern, '800-273-8255', $input);
  // Remove area code prefixes.
  $output = str_replace('1-800-273-8255', '800-273-8255', $output);
  // All instances should now be 800-273-8255 and can be replaced.
  if ($plain) {
    $output = str_replace('800-273-8255', $replacement_string, $output);
  }
  else {
    $output = str_replace('800-273-8255', $replacement_html, $output);
  }
  return $output;
}
