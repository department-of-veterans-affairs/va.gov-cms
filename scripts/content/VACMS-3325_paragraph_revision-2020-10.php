<?php

/**
 * @file
 * One-time migration for fixing the default revision for paragraphs.
 *
 * We are accessing the database tables directly instead of using the Field
 *   Storage API so
 */

run();

function run() {
  print('Finding Nodes which need to be updated...' . PHP_EOL);
  $sandbox = ['#finished' => 0, 'pids' => [], 'current' => 0];
  $sandbox['paragraph_ids'] = getAllParagraphIds();
  $sandbox['total'] = count($sandbox['paragraph_ids']);
  print('.');

  do {
    getVidsToUpdate($sandbox);
    print('.');
  } while ($sandbox['total'] - $sandbox['current'] > 0);

  print(PHP_EOL . 'Updating Paragraphs' . PHP_EOL);

  updateParagraphs($sandbox['pids']);
}

function getVidsToUpdate(&$sandbox) {
  $limit = 25;
  $paragraph_ids = array_slice($sandbox['paragraph_ids'], $sandbox['current'], $limit, TRUE);

  $new_pids = runRange($paragraph_ids);
  if ($new_pids) {
    $sandbox['pids'] += $new_pids;
  }

  $sandbox['current'] += count($paragraph_ids);
}

function runRange($paragraph_ids) : array {
  $pids = [];
  try {
    $paragraphs = getParagraphData($paragraph_ids);
    foreach ($paragraphs as $paragraph) {
      if (shouldWeUpdateThisParagraph($paragraph)) {
        $pids[$paragraph->id()] = $paragraph;
      }
    }
  }
  catch (\Exception $e) {
    \Drupal::logger('va_gov_db')
      ->error('Exception during paragraph migration');

    watchdog_exception('va_gov_db', $e);
  }

  return $pids;
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

function updateParagraphs($paragraphs) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  foreach ($paragraphs as $paragraph) {
    $node = $paragraph->getParentEntity();

    $nid = $node->id();
    $paragraph_field_items = clone $node->get($paragraph->parent_field_name->value);
    $paragraph_field_items->filter(function($item) use ($paragraph) {
      return $item->target_id === $paragraph->id();
    });

    $paragraph_field_item = $paragraph_field_items->first();
    $vid = $paragraph_field_item->target_revision_id;

    print('Updating paragraph ' . $paragraph->id() . ' on node ' . $nid . ' to use vid ' . $vid . PHP_EOL);
    $query = \Drupal::database()->update('paragraphs_item');
    $query->fields(['revision_id' => $vid]);
    $query->condition('id', $paragraph->id());
    $query->execute();

    $query = \Drupal::database()->update('paragraphs_item_field_data');
    $query->fields(['revision_id' => $vid]);
    $query->condition('id', $paragraph->id());
    $query->execute();
  }
}
