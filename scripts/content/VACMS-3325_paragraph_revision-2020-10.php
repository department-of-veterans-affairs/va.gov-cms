<?php

/**
 * @file
 * One-time migration for fixing the default revision for paragraphs.
 *
 * We are accessing the database tables directly instead of using the Field Storage API
 * so
 */

$sandbox = ['#finished' => 0];
do {
  print(run($sandbox));
} while ($sandbox['#finished'] < 1);

function run(&$sandbox) {
  if (!isset($sandbox['total'])) {
    $sandbox['total'] = getParagraphCount();
    $sandbox['paragraph_ids'] = getAllParagraphIds();
    $sandbox['current'] = 0;
  }

  $limit = 25;
  $paragraph_ids = array_slice($sandbox['paragraph_ids'], $sandbox['current'], $limit, TRUE);
  runRange($paragraph_ids);

  $sandbox['current'] += count($paragraph_ids);

  // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->info('Paragraph %current saved to fix revision ids. Paragraphs processed: %paragraph_ids', [
      '%current' => $sandbox['current'],
      '%paragraph_ids' => implode(', ', $paragraph_ids),
    ]);

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);

  // Log the all-finished notice.
  if ($sandbox['#finished'] == 1) {
    Drupal::logger('va_gov_db')->info('RE-saving all %count paragraphs completed.', [
      '%count' => $sandbox['total'],
    ]);
    return "Paragraph revision fix complete. {$sandbox['current']} / {$sandbox['total']}\n";
  }

  return "Processing paragraphs...{$sandbox['current']} / {$sandbox['total']}\n";
}

function runRange($paragraph_ids) {
  $connection = \Drupal::database();
  $transaction = $connection->startTransaction();

  try {
    $paragraphs = getParagraphData($paragraph_ids);
    foreach ($paragraphs as $paragraph) {

      if (shouldWeUpdateThisParagraph($paragraph)) {
        updateNodeForNewParagraph($paragraph->getParentEntity());
      }
    }
  }
  catch (\Exception $e) {
    $transaction->rollBack();
    \Drupal::logger('va_gov_db')
      ->error('Exception during paragraph migration');

    watchdog_exception('va_gov_db', $e);
  }
}

function getParagraphCount() : int {
  return \Drupal::entityQuery('paragraph')->count()->execute();
}

function getAllParagraphIds() {
  $query = \Drupal::entityQuery('paragraph');
  return $query->execute();
}

/**
 * @return \Drupal\paragraphs\Entity\Paragraph[]
 */
function getParagraphData(array $pids) : array {
  return \Drupal::entityTypeManager()->getStorage('paragraph')->loadMultiple($pids);
}

function shouldWeUpdateThisParagraph(\Drupal\paragraphs\Entity\Paragraph $paragraph) : bool {
  if ($paragraph->parent_type->value !== 'node') {
    return FALSE;
  }

  $parent = $paragraph->getParentEntity();
  $field_name = $paragraph->parent_field_name;

  if ($parent === NULL || $field_name === NULL) {
    return FALSE;
  }

  // Get the data for the field
  $paragraph_field_items = $parent->get($field_name->value);
  $paragraph_field_item = $paragraph_field_items->filter(function($item) use ($paragraph) {
    return $item->target_id === $paragraph->id() && $paragraph->getRevisionId() !== $item->target_revision_id;
  });

  return count($paragraph_field_item) > 0;
}

function updateNodeForNewParagraph(\Drupal\node\Entity\Node $node) {
  // Make this change a new revision.
  $node->setNewRevision(TRUE);

  // Set revision author to uid 1317 (CMS Migrator user).
  $node->setRevisionAuthorId(1317);
  $node->setChangedTime(time());
  $node->setRevisionCreationTime(time());
  $node->setOwnerId(1317);

  // Set revision log message.
  $node->setRevisionLogMessage('Resaved to fix out of sync data.');
  $node->save();
}
