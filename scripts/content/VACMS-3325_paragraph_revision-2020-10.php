<?php

/**
 * @file
 * One-time migration for fixing the default revision for paragraphs.
 *
 * We are accessing the database tables directly instead of using the Field Storage API
 * so
 */

//$paragraph_ids = [3784, 3805, 3807, 3809, 5510];
//$new_nids = runRange($paragraph_ids);
//updateNodes($new_nids);
run();

function run() {
  print('Finding Nodes which need to be updated...' . PHP_EOL);
  $sandbox = ['#finished' => 0, 'nids' => [], 'current' => 0];
  $sandbox['paragraph_ids'] = getAllParagraphIds();
  $sandbox['total'] = count($sandbox['paragraph_ids']);
  print('.');

  do {
    getVidsToUpdate($sandbox);
    print('.');
  } while ($sandbox['current'] / $sandbox['total'] >= 1);

  print('Re-saving nodes');

  updateNodes($sandbox['nids']);
}

function getVidsToUpdate(&$sandbox) {
  $limit = 25;
  $paragraph_ids = array_slice($sandbox['paragraph_ids'], $sandbox['current'], $limit, TRUE);

  $new_nids = runRange($paragraph_ids);
  if ($new_nids) {
    $sandbox['nids'] += $new_nids;
  }

  $sandbox['current'] += count($paragraph_ids);
}

function runRange($paragraph_ids) : array {
  $nids = [];
  try {
    $paragraphs = getParagraphData($paragraph_ids);
    foreach ($paragraphs as $paragraph) {
      if (shouldWeUpdateThisParagraph($paragraph)) {
        $nids[$paragraph->parent_id->value] = $paragraph->parent_id->value;
      }
    }
  }
  catch (\Exception $e) {
    \Drupal::logger('va_gov_db')
      ->error('Exception during paragraph migration');

    watchdog_exception('va_gov_db', $e);
  }

  return $nids;
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

  $paragraph_field_items = clone $parent->get($field_name->value);
  $paragraph_field_items->filter(function($item) use ($paragraph) {
    return $item->target_id === $paragraph->id() && $paragraph->getRevisionId() !== $item->target_revision_id;
  });

  return count($paragraph_field_items) > 0;
}

function updateNodes($nids) {
  /** @var \Drupal\node\NodeStorage $node_storage */
  $node_storage = Drupal::entityTypeManager()->getStorage('node');
  foreach ($nids as $nid) {
    print($nid . PHP_EOL);
    $vid = $node_storage->getLatestRevisionId($nid);
    updateNodeForNewParagraph(node_revision_load($vid));
  }
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
