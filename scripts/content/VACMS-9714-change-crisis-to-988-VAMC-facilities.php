<?php

/**
 * @file
 * Replace 800-273-8255 with 988
 *
 * VACMS-8935-scrub-tz-in-hours-field-comment.php.
 */

use Psr\Log\LogLevel;

$ops_status_total_pattern = "(\<a.*\>)?(1[\-\.])?800[\-\.]273[\-\.]8255(\<\/a\>)?";
$ops_status_pattern = "/(\<a.*\>)?(1[\-\.])?800[\-\.]273[\-\.]8255(\<\/a\>)?/i";
$replacement_string = '<a aria-label="9 8 8" href="tel:988">988</a>';


$sandbox = ['#finished' => 0];
do {
  print(va_gov_change_crisis_hotline_to_988_nodes($sandbox, $ops_status_total_pattern, $ops_status_pattern, $replacement_string));
} while ($sandbox['#finished'] < 1);
// Node processing complete.  Call this done.
return;

/**
 * Remove timezones from hours field comments.
 *
 * @param array $sandbox
 *   Modeling the structure of hook_update_n sandbox.
 *
 * @return string
 *   Status message.
 */
function va_gov_change_crisis_hotline_to_988_nodes(array &$sandbox, $pattern_string, $pattern_regex, $replacement_string)
{

  // Get the node count for nodes with timezones in hours comments.
  // This runs only once.
  if (!isset($sandbox['total'])) {

    // Find paragraphs with old number in the wysiwyg

    $paragraph_query = Drupal::database()->select('paragraph__field_wysiwyg', 'wysiwyg');
    $paragraph_query->join('node__field_content_block', 'nfcb', 'wysiwyg.entity_id = nfcb.field_content_block_target_id');
    $paragraph_query->fields('nfcb', ['entity_id']);
    $paragraph_query->groupBy('entity_id');
    $paragraph_query->condition('wysiwyg.field_wysiwyg_value', $pattern_string, 'REGEXP');

    $nids_to_update = $paragraph_query->execute()->fetchAllKeyed();
    $result_count = count($nids_to_update);
    $sandbox['total'] = $result_count;
    $sandbox['current'] = 0;
    $sandbox['nids_to_update'] = $nids_to_update;
    // $sandbox['nids_to_update'] = array_combine(
    //   array_map('_va_gov_stringifynid', array_values($nids_to_update)),
    //   array_values($nids_to_update));
  }


  // Do not continue if no nodes are found.
  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return "No nodes found for processing.\n";
  }

  $limit = 25;
  // Load entities.
  // $node_ids = array_keys($sandbox['nids_to_update']);
  $node_ids = array_keys($sandbox['nids_to_update']);

  $node_storage = \Drupal::entityTypeManager()->getStorage('node');

  foreach ($node_ids as $nid) {
    $node = $node_storage->loadRevision($node_storage->getLatestRevisionId($nid));
    $target_id = $sandbox['nids_to_update'][$nid];
    foreach ($node->field_content_block as $block) {
      /** @var Entity (i.e. Node, Paragraph, Term) $referenced_product **/
      $referenced_wysiwyg = $block->entity;
      $referenced_wysiwyg_id = $referenced_wysiwyg->id->getValue()[0]['value'];


    // Use now the entity to get the values you need.
    $field_value = $referenced_wysiwyg->field_wysiwyg->value;
    print_r($field_value);
  }
    }

      // Make this change a new revision.
        /** @var \Drupal\node\NodeInterface $node */
        $node->setNewRevision(TRUE);


        // Get the referenced entities
        $field_value = $node->get('field_content_block');
        $field_items = $field_value->getValue();
        $referenced_entities = $field_value->referencedEntities();
        $database=\Drupal::database();


        // replace the pattern-matching string in the appropriate entity
        foreach ($referenced_entities as $ref) {
          $refid = $ref->id->getValue()[0]['value'];
          if ($refid == $target_id) {
            // print($target_id);
            $old_value = $ref->field_wysiwyg->value;
            $new_value = preg_replace($pattern_regex, $replacement_string, $old_value);
            $ref->field_wysiwyg->setValue(
              [
                'value' => $new_value,
                'format' => 'rich_text',
              ]
              );
              $revId = $ref->getRevisionId();
              // print($revId);
              // $node->createRevision($refid); // throws error

              // TODO: Save paragraph entity with changes.

              /* This didn't work
              $ref->setRevisionUserId(1317);
              $ref->setChangedTime(time());
              $ref->setRevisionCreationTime(time());
              $ref->setRevisionLogMessage('Updated Crisis number from 800-237-8255 to 988');
              $ref->setSyncing(TRUE);
              $ref->save();
              */

            }
        }

        // Set revision author to uid 1317 (CMS Migrator user).
        $node->setRevisionUserId(1317);
        $node->setChangedTime(time());
        $node->setRevisionCreationTime(time());
        $node->setRevisionLogMessage('Updated Crisis number from 800-237-8255 to 988');
        $node->setSyncing(TRUE);
        $node->save();

        // Add to array the most recently-processed node
        unset($sandbox['nids_to_update']["{$node->id()}"]);
        $nids[] = $node->id();
        $sandbox['current'] = $sandbox['total'] - count($sandbox['nids_to_update']);

    }

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Nodes: %current nodes phone numbers updated. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => implode(', ', $nids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  //   // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->log(LogLevel::INFO, 'RE-saving all %count nodes completed by change-crisis-to-988', [
      '%count' => $sandbox['total'],
    ]);
    return "Node updates complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processed nodes... {$sandbox['current']} / {$sandbox['total']}.\n";



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
