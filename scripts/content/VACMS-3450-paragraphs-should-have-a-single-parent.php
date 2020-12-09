<?php

/**
 * @file
 * One-time migration for fixing improperly cloned paragraphs.
 *
 * Users of the entity_clone form were deselecting the checkboxes for
 * Paragraphs entities, which led to cloned nodes having references to
 * the original node's Paragraphs.
 *
 * Our approach here will be:
 *
 * 1. Retrieve a list of all paragraphs with multiple parents.
 * 2. Repair the nodes owning those paragraphs:
 *   A. Determine whether it is a cloned node; if not, no action is necessary.
 *   B. Clone the paragraph.
 *   C. Update the cloned node to refer instead to the cloned paragraph.
 *   D. Save the node.
 * 3. Confirm that no paragraphs exist in the original condition.
 *
 */

use Drupal\entity_clone\EntityClone\EntityCloneInterface;
use Drupal\entity_clone\EntityClone\Content\ContentEntityCloneBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Log a message both to stdout and the Drupal logger.  NOT.
 *
 * @param string $message
 *   The message to log.
 */
function logMessage(string $message): void {
  // \Drupal::logger(__FILE__)->notice($message);
  echo $message . PHP_EOL;
}

/**
 * Retrieve a list of all paragraph item fields on all entities.
 *
 * @return object[]
 *  A list of stdClass objects:
 *    - parent_type (string): an entity type machine name.
 *    - parent_field_name (string): an field machine name.
 */
function getParagraphsFields(): array {
  $database = \Drupal::database();

  $query = $database->select('paragraphs_item_field_data', 'p');
  $query->addField('p', 'parent_type', 'parent_type');
  $query->addField('p', 'parent_field_name', 'parent_field_name');
  $result = $query->distinct()->execute()->fetchAll();

  return $result;
}

/**
 * Retrieve a list of misparented paragraph field items with a given parent type and field.
 *
 * This retrieves a list of paragraph field items where the parent points to a paragraph revision
 *  but the paragraph points to a different parent.
 *
 * This function exists purely for documentary purposes, as it indicates a separate set of
 *  paragraph entities with a distinct but similar problem.
 *
 * @param string $parent_type
 *   The machine name of the parent entity type.
 * @param string $parent_field_name
 *   The machine name of the parent entity field.
 *
 * @return object[]
 *   A list of stdClass objects:
 *     - target_id (string): a paragraph entity ID.
 *     - revision_id (string): a paragraph entity revision ID.
 *     - parent_id (string): an entity ID.
 *     - parent_type (string): an entity type machine name.
 *     - parent_field_name (string): a field machine name.
 */
function getMisparentedParagraphFieldRows(string $parent_type, string $parent_field_name): array {
  $database = \Drupal::database();

  $query = $database->select("{$parent_type}__{$parent_field_name}", 'parent');
  $query->join('paragraphs_item_field_data', 'paragraph', "parent.{$parent_field_name}_target_id = paragraph.id AND parent.{$parent_field_name}_target_revision_id = paragraph.revision_id AND parent.entity_id <> paragraph.parent_id");
  $query->addField('parent', 'entity_id', 'parent_id');
  $query->addField('parent', "{$parent_field_name}_target_id", 'target_id');
  $query->addField('parent', "{$parent_field_name}_target_revision_id", 'revision_id');
  $statement = $query->execute();

  $result = [];
  foreach ($statement as $item) {
    $result[] = (object)[
      'target_id' => $item->target_id,
      'revision_id' => $item->revision_id,
      'parent_id' => $item->parent_id,
      'parent_type' => $parent_type,
      'parent_field_name' => $parent_field_name,
    ];
  }

  $count = count($result);
  logMessage("{$parent_type}: {$parent_field_name}... {$count} misparented field rows found.");

  return $result;
}

/**
 * Retrieve a list of multiply parented paragraph fields with a given parent type and field.
 *
 * Given a parent type and parent field name, we should fetch all of the paragraphs entities
 *  whose revisions do not share a common parent_id.
 *
 * If a paragraph's parent_id changes from revision to revision, it may be the result of an
 *  improper clone.
 *
 * @param string $parent_type
 *   The machine name of the parent entity type.
 * @param string $parent_field_name
 *   The machine name of the parent entity field.
 *
 * @return object[]
 *   A list of stdClass objects, keyed by the paragraph ID:
 *     - target_id (integer): an entity ID.
 *     - parent_ids (array): a list of entity IDs.
 *     - parent_type (string): an entity type machine name.
 *     - parent_field_name (string): a field machine name.
 */
function getMultiplyParentedParagraphFieldRows(string $parent_type, string $parent_field_name): array {
  $database = \Drupal::database();

  $query = $database->select('paragraphs_item_revision_field_data', 'revision');
  $query->condition('parent_type', $parent_type);
  $query->condition('parent_field_name', $parent_field_name);
  $query->addField('revision', 'id', 'target_id');
  $query->addField('revision', 'parent_id', 'parent_id');
  $statement = $query->distinct()->execute();

  $result = [];
  foreach ($statement as $item) {
    $target_id = $item->target_id;
    if (empty($result[$target_id])) {
      $result[$target_id] = (object)[
        'target_id' => $target_id,
        'parent_ids' => [
          $item->parent_id,
        ],
        'parent_type' => $parent_type,
        'parent_field_name' => $parent_field_name,
      ];
    }
    else {
      $result[$target_id]->parent_ids[] = $item->parent_id;
      sort($result[$target_id]->parent_ids);
    }
  }

  $result = array_filter($result, function ($row) {
    return count($row->parent_ids) > 1;
  });

  $count = count($result);
  logMessage("{$parent_type}: {$parent_field_name}... {$count} multiply-parented field rows found.");

  return $result;
}

/**
 * Clone a paragraph and prepare it for insertion into the original node.
 *
 * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $cloner
 *  A cloner object.
 * @param \Drupal\paragraphs\ParagraphInterface $paragraph
 *   The paragraph to clone.
 * @param int $parent_id
 *   The parent ID, used to strip unwanted revisions.
 * @param array $properties
 *  Properties used to create the clone paragraph.
 * @param array& $already_cloned
 *  A list of entities already cloned, for use by entity_clone.
 *
 * @return \Drupal\paragraphs\ParagraphInterface
 *   The cloned paragraph.
 */
function cloneParagraph(EntityCloneInterface $cloner, ParagraphInterface $paragraph, int $parent_id, array $properties, array &$already_cloned): ParagraphInterface {
  $clone = $paragraph->createDuplicate();
  $result = $cloner->cloneEntity($paragraph, $clone, $properties, $already_cloned);
  return $result;
}

/**
 * Replace a paragraph with its clone.
 *
 * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $cloner
 *  A cloner object.
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *  The host entity.
 * @param string $field_name
 *  The name of the paragraph field.
 * @param int $pid
 *  The paragraph entity ID to clone and replace.
 * @param array $properties
 *  Properties used to create the clone paragraph.
 * @param array& $already_cloned
 *  A list of entities already cloned, for use by entity_clone.
 */
function replaceParagraph(EntityCloneInterface $cloner, ContentEntityInterface $entity, string $field_name, int $pid, array $properties, array &$already_cloned): void {
  $field_value = $entity->get($field_name);
  $field_items = $field_value->getValue();
  $referenced_entities = $field_value->referencedEntities();
  $database = \Drupal::database();

  foreach ($field_items as $key => $field_item) {
    if (intval($field_item['target_id']) === $pid) {
      $referenced_entity = $referenced_entities[$key];
      $clone = cloneParagraph($cloner, $referenced_entity, $entity->id(), $properties, $already_cloned);

      $query = $database->update("{$entity->getEntityTypeId()}__{$field_name}");
      $query->fields([
        "{$field_name}_target_id" => $clone->id(),
        "{$field_name}_target_revision_id" => $clone->getRevisionId(),
      ]);
      $query->condition('entity_id', $entity->id());
      $query->condition('revision_id', $entity->getRevisionId());
      $query->condition('delta', $key);
      $query->execute();

      $query = $database->update("{$entity->getEntityTypeId()}_revision__{$field_name}");
      $query->fields([
        "{$field_name}_target_id" => $clone->id(),
        "{$field_name}_target_revision_id" => $clone->getRevisionId(),
      ]);
      $query->condition('entity_id', $entity->id());
      $query->condition('revision_id', $entity->getRevisionId());
      $query->condition('delta', $key);
      $query->execute();

      logMessage("Updated node #{$entity->id()} \"{$entity->getTitle()}\" value #{$key} with new paragraph (was {$pid}, now {$clone->id()}).");
      break;
    }
  }

}

/**
 * Process an improperly cloned paragraph.
 *
 * @param string $parent_type
 *   The type of entity parenting this paragraph.
 * @param string $parent_field_name
 *   The machine name of the parent entity.
 * @param int $id
 *   The paragraph entity ID.
 * @param int[] $parent_ids
 *   The parents with improper references to this paragraph.
 */
function processParagraph(string $parent_type, string $parent_field_name, int $target_id, array $parent_ids): void {
  $entity_type_manager = \Drupal::entityTypeManager();
  $cloner = new ContentEntitycloneBase($entity_type_manager, $parent_type);
  $parent_storage = $entity_type_manager->getStorage($parent_type);
  $parents = $parent_storage->loadMultiple($parent_ids);
  $already_cloned = [];
  $properties = [
    'children' => [],
  ];
  foreach ($parents as $parent_id => $parent) {
    replaceParagraph($cloner, $parent, $parent_field_name, $target_id, $properties, $already_cloned);
  }
}

/**
 * Delete non-default revisions of the specified nodes.
 *
 * @param int[] $nids
 *   The nodes whose revision histories should be deleted.
 */
function deleteRevisionHistoryForNodes(array $nids) {
  $node_storage = \Drupal::entityManager()->getStorage('node');
  $nodes = $node_storage->loadMultiple($nids);
  foreach ($nodes as $node) {
    $default_revision_id = $node->getRevisionId();
    $revision_ids = array_diff($node_storage->revisionIds($node), [ $default_revision_id ]);
    foreach ($revision_ids as $revision_id) {
      logMessage("Deleting old revision #$revision_id for node #{$node->id()}.");
      $node_storage->deleteRevision($revision_id);
    }
  }
}

/**
 * Main entry point for our script.
 *
 * The affected paragraphs are paragraphs that have revisions whose parent ids are different.
 *
 * After retrieving a list of these paragraphs, we iterate through them, and:
 *   - Skip the first parent; it will keep its paragraph.
 *   - For each remaining parent:
 *     - Clone the paragraph, and
 *     - Update the parent accordingly.
 */
function run() {
  $paragraph_fields = getParagraphsFields();

  foreach ($paragraph_fields as $paragraph_field) {
    $parent_type = $paragraph_field->parent_type;
    $parent_field_name = $paragraph_field->parent_field_name;

    // We only care about cloned nodes right now, because nested paragraphs
    // should clone cleanly by default.
    if ($parent_type !== 'node') {
      logMessage("Not processing parent_type $parent_type, field $parent_field_name...");
      continue;
    }

    $rows = getMultiplyParentedParagraphFieldRows($parent_type, $parent_field_name);
    foreach ($rows as $target_id => $row) {
      $parent_ids = $row->parent_ids;
      $parent_count = count($parent_ids);

      if ($parent_count > 0) {
        $parent_list = implode(', ', $parent_ids);
        logMessage("Paragraph #{$target_id} has {$parent_count} parents: {$parent_list}");
        // We can leave this paragraph with its original parent,
        // but we wanna give clones to its other parents.
        $cloned_parent_ids = array_slice($parent_ids, 1);
        processParagraph($parent_type, $parent_field_name, $target_id, $cloned_parent_ids);
        // Those improperly cloned nodes should also lose their revision histories to avoid
        // reoccurrences of this very problem.
        deleteRevisionHistoryForNodes($cloned_parent_ids);
      }
    }

  }
}

run();
