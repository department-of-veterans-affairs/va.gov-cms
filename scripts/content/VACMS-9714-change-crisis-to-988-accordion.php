<?php

/**
 * @file
 * Replace 800-273-8255 with 988
 *
 * VACMS-8935-scrub-tz-in-hours-field-comment.php.
 */

use Psr\Log\LogLevel;

$ops_status_total_pattern = "(\<a.*href\=\"tel:18002738255\"\>)?(1[\-\.])?800[\-\.]273[\-\.]8255(\<\/a\>)?";
$ops_status_pattern = "/(\<a.*href\=\"tel:18002738255\"\>)?(1[\-\.])?800[\-\.]273[\-\.]8255(\<\/a\>)?/i";
$replacement_string = '<a aria-label="9 8 8" href="tel:988">988</a>';


$parabox = ['#finished' => 0];
do {
  print(va_gov_change_crisis_hotline_to_988_nodes($parabox, $ops_status_total_pattern, $ops_status_pattern, $replacement_string));
} while ($parabox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Remove timezones from hours field comments.
 *
 * @param array $parabox
 *   Modeling the structure of hook_update_n $parabox.
 *
 * @return string
 *   Status message.
 */
function va_gov_change_crisis_hotline_to_988_nodes(array &$parabox, $pattern_string, $pattern_regex, $replacement_string) {
    $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');


  // Get the node count for nodes with timezones in hours comments.
  // This runs only once.
  if (!isset($parabox['total'])) {
    $query = $paragraph_storage->getQuery();
    $query->condition('type', 'collapsible_panel_item');
    $pids_to_update = $query->execute();

    // Create non-numeric keys to accurately remove each nid when processed.
    $parabox['pids_to_update'] = array_combine(
        array_map('_va_gov_stringifypid', array_values($pids_to_update)),
        array_values($pids_to_update));
    }
    // assumes fieldName is a text field with 1 value
    function filterNodesByRegex($pids, $fieldName, $regex) {
      $paragraphs = \Drupal::entityTypeManager()->getStorage('paragraph')->loadMultiple($pids);
      return array_filter($paragraphs, function($paragraph) use ($regex, $fieldName) {
        return preg_match(
          $regex,
          $paragraph->get($fieldName)->getString()
        );
      });
    }

    $pids_to_update = filterNodesByRegex($pids_to_update, 'field_wysiwyg', $pattern_regex);
    $pids_count = count($pids_to_update);
    $parabox['paragraph_total'] = $pids_count;
    $parabox['paragraph_current'] = 0;


    foreach($pids_to_update as $accordion) {
      $parent = $accordion->getParentEntity();
      $node = $parent->getParentEntity();
      $field_value = $accordion->field_wysiwyg->getValue()[0]['value'];
      $new_value = preg_replace($pattern_regex, $replacement_string, $field_value);
      // TODO: Get the $new_value to set
      $accordion->field_wysiwyg->setValue(
        [
          'value' => $new_value,
          'format' => 'rich_text',
        ]
        );
      $node->setNewRevision(TRUE);
      $node->setRevisionUserId(1317);
      $node->setChangedTime(time());
      $node->setRevisionCreationTime(time());
      $node->setRevisionLogMessage('Updated Crisis number from 800-237-8255 to 988');
      $node->setSyncing(TRUE);
      $node->save();
    }

  // Do not continue if no nodes are found.
  if (empty($parabox['total'])) {
    $parabox['#finished'] = 1;
    return "No nodes found for processing.\n";
  }
  // TODO: fix the counter. It is not working!
  unset($parabox['pids_to_update'][_va_gov_stringifypid($accordion->id())]);
  $pids[] = $accordion->id();
  $parabox['paragraph_current'] = $parabox['paragraph_total'] - count($parabox['pids_to_update']);

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
  ->log(LogLevel::INFO, 'Nodes: %current nodes phone numbers updated. Nodes processed: %pids', [
    '%current' => $parabox['current'],
    '%pids' => implode(', ', $pids),
  ]);

  $parabox['#finished'] = ($parabox['current'] / $parabox['total']);

  //   // Log the all-finished notice.
  if ($parabox['#finished'] == 1) {
  Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count nodes completed by change-crisis-to-988', [
    '%count' => $parabox['total'],
  ]);
  return "Node updates complete. {$parabox['current']} / {$parabox['total']}\n";
  }

  return "Processed nodes... {$parabox['current']} / {$parabox['total']}.\n";
}

/**
 * Callback function to concat node ids with string.
 *
 * @param int $nid
 *   The node id.
 *
 * @return string
 *   The node id concatenated to the end of node_
 */
function _va_gov_stringifynid($nid) {
  return "node_$nid";
}
/**
 * Callback function to concat paragraph ids with string.
 *
 * @param int $pid
 *   The paragraph id.
 *
 * @return string
 *   The paragraph id appended to the end of paragraph_.
 */
function _va_gov_stringifypid($pid) {
    return "paragraph_$pid";
  }
